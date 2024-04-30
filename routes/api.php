<?php

use App\Spotify\Facades\Spotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/spotify/auth', fn () => response()->json(['url' => Spotify::authUrl('https://jamfluencer.app/auth/spotify/callback')]))
        ->middleware(['auth:sanctum']);
    Route::post('/spotify/auth', function (Request $request) {
        $request->validate(['code'=>['required','string']]);
        $token = Spotify::accessToken($request->input('code'));
        $request->user()->spotifyToken()->create([
            'token' => $token->token,
            'scope'=>$token->scopes->implode(','),
            'expiry'=>$token->expiry,
            'refresh'=>$token->refresh
        ]);

        return response()->noContent();
    })
        ->middleware(['auth:sanctum']);
    Route::get('/me', fn (Request $request) => $request->user())->middleware(['auth:sanctum']);
});
