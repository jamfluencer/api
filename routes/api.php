<?php

use App\Spotify\Facades\Spotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/auth/spotify', fn () => response()->json(['url' => Spotify::authUrl()]))
        ->middleware(['auth:sanctum']);
    Route::post('/auth/spotify/callback', function (Request $request) {

        return response()->noContent();
    })
        ->middleware(['auth:sanctum']);
    Route::get('/me', fn (Request $request) => $request->user())->middleware(['auth:sanctum']);
});
