<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', fn () => response()->json(['status' => 'OK']));

Route::get(
    '/auth/google',
    fn () => response()->json(['url'=>Socialite::driver('google')->redirect()->getTargetUrl()])
);
Route::get('/auth/google/callback', function () {
    $google = Socialite::driver('google')->user();

    /** @noinspection PhpParamsInspection */
    Auth::login(User::query()->firstOrCreate(
        [
            'email' => $google->getEmail(),
        ],
        ['email_verified_at' => now()]
    ));

    return redirect('https://influencer.app/');
});
