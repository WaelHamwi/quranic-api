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
        /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
        $provider = Socialite::driver('google');
        return $provider->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
        $provider = Socialite::driver('google');
        $googleUser = $provider->stateless()->user();

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
                $oauthProvider->update(['provider_token' => $accessToken]);
                $user = $oauthProvider->user;
                return response()->json([
                    'status' => 'success',
                    'user'   => $user,
                    'token'  => $user->createToken('mobile-app')->plainTextToken,
                ]);
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

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
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

        return response()->json([
            'status' => 'success',
            'user'   => $user->fresh(),
            'token'  => $user->createToken('mobile-app')->plainTextToken,
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
