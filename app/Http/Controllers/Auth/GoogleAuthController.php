<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $provider = Socialite::driver('google');
        assert($provider instanceof \Laravel\Socialite\Two\AbstractProvider);
        return $provider->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $provider = Socialite::driver('google');
        assert($provider instanceof \Laravel\Socialite\Two\AbstractProvider);
        $googleUser = $provider->stateless()->user();
        assert($googleUser instanceof \Laravel\Socialite\Two\User);

        DB::beginTransaction();
        try {
            $oauthProvider = OAuthProvider::where('provider', 'google')
                ->where('provider_user_id', $googleUser->getId())
                ->first();

            if ($oauthProvider) {
                $user = $oauthProvider->user;
            } else {
                $user = User::where('email', $googleUser->getEmail())->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                        'email' => $googleUser->getEmail(),
                        'email_verified_at' => now(),
                        'password' => bcrypt(Str::random(32)),
                    ]);
                }
                $user->oauthProviders()->create([
                    'provider' => 'google',
                    'provider_user_id' => $googleUser->getId(),
                    'provider_token' => $googleUser->token,
                    'provider_refresh_token' => $googleUser->refreshToken ?? null,
                ]);
            }
            DB::commit();
            Auth::login($user);
            return redirect('/admin');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect('/login')->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }

    public function handleMobileGoogleCallback(Request $request)
    {
        $request->validate([
            'code'           => 'required|string',
            'code_verifier'  => 'required|string',
        ]);

        try {
            $client = new \GuzzleHttp\Client(['verify' => false]);

            // Exchange PKCE authorization code for tokens server-side.
            $tokenResponse = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code'          => $request->input('code'),
                    'client_id'     => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'redirect_uri'  => config('services.google.redirect'),
                    'grant_type'    => 'authorization_code',
                    'code_verifier' => $request->input('code_verifier'),
                ],
            ]);

            $tokens      = json_decode($tokenResponse->getBody(), true);
            $accessToken = $tokens['access_token'];

            // Fetch user profile.
            $userInfoResponse = $client->get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            ]);

            $googleUser = json_decode($userInfoResponse->getBody(), true);

            // Existing Google account — log in directly.
            $oauthProvider = OAuthProvider::where('provider', 'google')
                ->where('provider_user_id', $googleUser['sub'])
                ->first();

            if ($oauthProvider) {
                $user = $oauthProvider->user;
                if ($user) {
                    $oauthProvider->update(['provider_token' => $accessToken]);
                    return response()->json([
                        'status' => 'success',
                        'user'   => $user,
                        'token'  => $user->createToken('mobile-app')->plainTextToken,
                    ]);
                }
                $oauthProvider->delete();
            }

            // Email registered via another method — link and log in.
            $existingUser = User::where('email', $googleUser['email'])->first();
            if ($existingUser) {
                $existingUser->oauthProviders()->create([
                    'provider'         => 'google',
                    'provider_user_id' => $googleUser['sub'],
                    'provider_token'   => $accessToken,
                ]);
                return response()->json([
                    'status' => 'success',
                    'user'   => $existingUser,
                    'token'  => $existingUser->createToken('mobile-app')->plainTextToken,
                ]);
            }

            // Brand-new user — generate OTP and ask for email verification.
            $otp   = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $email = $googleUser['email'];

            Cache::put("otp:{$email}", [
                'otp'            => Hash::make($otp),
                'google_sub'     => $googleUser['sub'],
                'google_token'   => $accessToken,
                'name'           => $googleUser['name'] ?? $googleUser['given_name'] ?? 'User',
                'email_verified' => $googleUser['email_verified'] ?? false,
            ], 600);

            Mail::to($email)->send(new OtpVerificationMail($otp));

            return response()->json([
                'status' => 'verification_required',
                'email'  => $email,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function handleGoogleMobileWebCallback(Request $request)
    {
        $stateRaw     = $request->query('state', '');
        $sessionToken = base64_decode(strtr($stateRaw, '-_', '+/') . str_repeat('=', (4 - strlen($stateRaw) % 4) % 4));
        $cacheKey     = "auth_session:{$sessionToken}";

        try {
            $driver = Socialite::driver('google');
            assert($driver instanceof \Laravel\Socialite\Two\AbstractProvider);
            $googleUser = $driver
                ->stateless()
                ->redirectUrl(config('services.google.mobile_redirect'))
                ->user();
            assert($googleUser instanceof \Laravel\Socialite\Two\User);
        } catch (\Exception $e) {
            Cache::put($cacheKey, ['status' => 'error', 'message' => $e->getMessage()], 300);
            return view('auth.complete', ['status' => 'error']);
        }

        // Existing Google account — log in directly.
        $oauthProvider = OAuthProvider::where('provider', 'google')
            ->where('provider_user_id', $googleUser->getId())
            ->first();

        if ($oauthProvider) {
            $user = $oauthProvider->user;
            if (! $user) {
                // Orphaned provider record — user was deleted. Clean up and treat as new user.
                $oauthProvider->delete();
            } else {
                $token = $user->createToken('mobile-app')->plainTextToken;
                Cache::put($cacheKey, [
                    'status' => 'success',
                    'token'  => $token,
                    'user'   => $user->only(['id', 'name', 'email', 'avatar_path']),
                ], 300);
                return view('auth.complete', ['status' => 'success']);
            }
        }

        // Email registered via another method — link and log in.
        $existingUser = User::where('email', $googleUser->getEmail())->first();
        if ($existingUser) {
            $existingUser->oauthProviders()->create([
                'provider'         => 'google',
                'provider_user_id' => $googleUser->getId(),
                'provider_token'   => $googleUser->token,
            ]);
            if (! $existingUser->google_id) {
                $existingUser->update([
                    'google_id'   => $googleUser->getId(),
                    'avatar_path' => $existingUser->avatar_path ?? $googleUser->getAvatar(),
                ]);
            }
            $token = $existingUser->createToken('mobile-app')->plainTextToken;
            Cache::put($cacheKey, [
                'status' => 'success',
                'token'  => $token,
                'user'   => $existingUser->fresh()->only(['id', 'name', 'email', 'avatar_path']),
            ], 300);
            return view('auth.complete', ['status' => 'success']);
        }

        // Brand-new user — send OTP to their email and show the browser OTP entry page.
        $otp   = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $email = $googleUser->getEmail();

        Cache::put("otp:{$email}", [
            'otp'            => Hash::make($otp),
            'google_sub'     => $googleUser->getId(),
            'google_token'   => $googleUser->token,
            'name'           => $googleUser->getName() ?? 'User',
            'avatar_url'     => $googleUser->getAvatar(),
            'email_verified' => true,
        ], 600);

        Mail::to($email)->send(new OtpVerificationMail($otp));

        return view('auth.otp-entry', [
            'email'        => $email,
            'sessionToken' => $sessionToken,
        ]);
    }

    public function getSessionResult(Request $request, string $token)
    {
        $cacheKey = "auth_session:{$token}";
        $result   = Cache::get($cacheKey);

        if (! $result) {
            return response()->json(['status' => 'pending'], 202);
        }

        Cache::forget($cacheKey);
        return response()->json($result);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email'         => 'required|email',
            'otp'           => 'required|string|size:6',
            'session_token' => 'nullable|string',
        ]);

        $cached = Cache::get("otp:{$request->email}");

        if (! $cached || ! Hash::check($request->otp, $cached['otp'])) {
            return response()->json(['error' => 'invalid_otp'], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'              => $cached['name'],
                'email'             => $request->email,
                'email_verified_at' => now(),
                'password'          => bcrypt(Str::random(32)),
                'google_id'         => $cached['google_sub'],
                'avatar_path'       => $cached['avatar_url'] ?? null,
            ]);

            $user->oauthProviders()->create([
                'provider'         => 'google',
                'provider_user_id' => $cached['google_sub'],
                'provider_token'   => $cached['google_token'],
            ]);

            $user->assignRole('user');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Registration failed'], 500);
        }

        Cache::forget("otp:{$request->email}");
        Cache::forget("otp_resend:{$request->email}");

        $token = $user->createToken('mobile-app')->plainTextToken;

        // When called from the browser OTP page, store result in session cache so the
        // app's polling loop can pick it up and dismiss the browser.
        if ($request->input('session_token')) {
            Cache::put("auth_session:{$request->input('session_token')}", [
                'status' => 'success',
                'token'  => $token,
                'user'   => $user->fresh()->only(['id', 'name', 'email', 'avatar_path']),
            ], 300);
        }

        return response()->json([
            'status' => 'success',
            'user'   => $user->fresh(),
            'token'  => $token,
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $cached = Cache::get("otp:{$request->email}");
        if (! $cached) {
            return response()->json(['error' => 'No pending verification for this email'], 422);
        }

        $resendKey   = "otp_resend:{$request->email}";
        $resendCount = Cache::get($resendKey, 0);
        if ($resendCount >= 3) {
            return response()->json(['error' => 'too_many_resend_attempts'], 429);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put("otp:{$request->email}", array_merge($cached, [
            'otp' => Hash::make($otp),
        ]), 600);

        Cache::put($resendKey, $resendCount + 1, 600);

        Mail::to($request->email)->send(new OtpVerificationMail($otp));

        return response()->json(['status' => 'sent']);
    }
}
