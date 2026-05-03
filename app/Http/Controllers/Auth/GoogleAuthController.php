<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

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
            'access_token' => 'required|string',
        ]);

        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
            $response = $client->get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $request->access_token,
                ],
            ]);

            $googleUser = json_decode($response->getBody(), true);

            DB::beginTransaction();
            try {
                $oauthProvider = OAuthProvider::where('provider', 'google')
                    ->where('provider_user_id', $googleUser['sub'])
                    ->first();

                if ($oauthProvider) {
                    $user = $oauthProvider->user;
                    $oauthProvider->update([
                        'provider_token' => $request->access_token,
                    ]);
                } else {
                    $user = User::where('email', $googleUser['email'])->first();
                    if (!$user) {
                        $user = User::create([
                            'name' => $googleUser['name'] ?? $googleUser['given_name'] ?? 'Google User',
                            'email' => $googleUser['email'],
                            'email_verified_at' => $googleUser['email_verified'] ? now() : null,
                            'password' => bcrypt(Str::random(32)),
                        ]);
                    }
                    $user->oauthProviders()->create([
                        'provider' => 'google',
                        'provider_user_id' => $googleUser['sub'],
                        'provider_token' => $request->access_token,
                    ]);
                }
                DB::commit();

                $token = $user->createToken('mobile-app');

                return response()->json([
                    'user' => $user,
                    'token' => $token->plainTextToken,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
