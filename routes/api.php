<?php

use App\Models\User;
use App\Spotify\Facades\Spotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::prefix('v1')->group(function () {
    Route::get('/auth/spotify', fn() => response()->json(['url' => Spotify::authUrl()]))
        ->middleware(['auth:sanctum']);
    Route::post('/auth/spotify/callback', function (Request $request) {


        return response()->noContent();
    })
        ->middleware(['auth:sanctum']);

    Route::get('/auth/google', fn() => Socialite::driver('google')->stateless()->redirect());
    Route::get('/auth/google/callback', function () {
        $google = Socialite::driver('google')->stateless()->user();

        return response()->json(['token' => User::firstOrCreate(
            [
                'email' => $google->getEmail(),
            ],
            ['email_verified_at' => now()]
        )->createToken('api')->plainTextToken]);
    });
});
