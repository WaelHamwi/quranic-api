<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// ── Mobile Google OAuth — server-side flow ─────────────────────────────────
// Step 1: mobile app opens this URL; we redirect to Google with returnTo in state.
Route::get('/auth/google/mobile', function (Request $request) {
    $returnTo = $request->query('returnTo', '');
    $state    = rtrim(strtr(base64_encode($returnTo), '+/', '-_'), '=');

    return Socialite::driver('google')
        ->stateless()
        ->redirectUrl(config('services.google.mobile_redirect'))
        ->with(['state' => $state])
        ->scopes(['openid', 'profile', 'email'])
        ->redirect();
});

// Step 2: Google redirects here; we decode state, get the user, redirect to app.
Route::get('/auth/google/mobile/callback', [GoogleAuthController::class, 'handleGoogleMobileWebCallback']);
