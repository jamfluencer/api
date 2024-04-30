<?php

use App\Spotify\AccessToken;
use App\Spotify\Facades\Spotify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/spotify/auth', fn () => response()
        ->json(['url' => Spotify::authUrl('https://jamfluencer.app/auth/spotify/callback')]));

    Route::post('/spotify/auth', function (Request $request) {
        $request->validate(['code' => ['required', 'string']]);
        $token = Spotify::accessToken($request->input('code'));
        $request->user()->spotifyToken()->create([
            'token' => $token->token,
            'scope' => $token->scopes->implode(','),
            'expires_at' => $token->expiresAt,
            'refresh' => $token->refresh,
        ]);

        return response()->noContent();
    });

    Route::get('/me', fn (Request $request) => $request->user());

    Route::get('/spotify/player/track', function (Request $request): JsonResponse {
        $track = Spotify::setToken(
            $request->user()->spotifyToken->forSpotify()->expired()
                ? tap(Spotify::refreshToken($request->user()->spotifyToken->forSpotify()),
                fn (AccessToken $refreshed) => $request->user()->spotifyToken->update([
                    'token' => $refreshed->token,
                    'refresh' => $refreshed->refresh,
                ]))
                : Spotify::refreshToken($request->user()->spotifyToken->forSpotify())
        )
        ->currentlyPlaying();

        return response()->json($track);
    });

    Route::get('/spotify/player/playlist', function (Request $request): JsonResponse {
        $track = Spotify::setToken(
            $request->user()->spotifyToken->forSpotify()->expired()
                ? tap(Spotify::refreshToken($request->user()->spotifyToken->forSpotify()),
                fn (AccessToken $refreshed) => $request->user()->spotifyToken->update([
                    'token' => $refreshed->token,
                    'refresh' => $refreshed->refresh,
                ]))
                : Spotify::refreshToken($request->user()->spotifyToken->forSpotify())
        )
            ->currentlyPlaying();

        return response()->json($track);
    });
});
