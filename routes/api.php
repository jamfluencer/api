<?php

use App\Models\User;
use App\Spotify\AccessToken;
use App\Spotify\Events\JamEnded;
use App\Spotify\Events\JamStarted;
use App\Spotify\Facades\Spotify;
use App\Spotify\Jobs\PollJam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/spotify/auth', fn() => response()
        ->json(['url' => Spotify::authUrl('https://jamfluencer.app/auth/spotify/callback')]));

    Route::post('/spotify/auth', function (Request $request) {
        $request->validate(['code' => ['required', 'string']]);
        $token = Spotify::accessToken($request->input('code'));
        $request->user()->spotifyToken()->delete();
        $request->user()->spotifyToken()->create([
            'token' => $token->token,
            'scope' => $token->scopes->implode(','),
            'expires_at' => $token->expiresAt,
            'refresh' => $token->refresh,
        ]);

        return response()->noContent();
    });

    Route::get('/me', fn(Request $request) => $request->user());

    Route::get('/spotify/player/track', function (Request $request): JsonResponse {
        if (Cache::has('jam') === false) {
            return response()->json(['message' => 'NO JAM FOR YOU'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        $track = Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', []), 'user'))->spotifyToken)
            ->currentlyPlaying();

        return response()->json($track);
    });

    Route::get('/spotify/player/queue', function (Request $request): JsonResponse {
        if (Cache::has('jam') === false) {
            return response()->json(['message' => 'NO JAM FOR YOU'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        $queue = Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', []), 'user'))->spotifyToken)
            ->queue();

        return response()->json($queue);
    });

    Route::get('/spotify/playlists/{id}', function (Request $request, string $id): JsonResponse {
        if (Cache::has('jam') === false) {
            return response()->json(['message' => 'NO JAM FOR YOU'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        $playlist = Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', []), 'user'))->spotifyToken)
            ->playlist($id);

        return response()->json($playlist);
    });

    Route::put('/jam/start/{playlist?}', function (Request $request, ?string $playlist = null): JsonResponse {
        if ($request->user()->spotifyToken === null) {
            return response()->json(['message' => 'No valid Spotify token for authenticated user.'], Response::HTTP_UNAUTHORIZED);
        }

        $spotify = Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', []), 'user'))->spotifyToken);
        $track = $spotify->play($playlist);

        Cache::put(
            'jam',
            [
                'user' => $request->user()->id,
                'playlist' => $playlist,
            ],
            60 * 60 * 8
        );

        JamStarted::dispatch(
            $spotify->playlist(Arr::last(explode(':', $playlist))),
            $spotify->queue()
        );

        PollJam::dispatchAfterResponse();

        return response()->json($track);
    });

    Route::put('/jam/stop', function (Request $request): JsonResponse {
        if ($request->user()->spotifyToken === null) {
            return response()->json(['message' => 'No valid Spotify token for authenticated user.'], Response::HTTP_UNAUTHORIZED);
        }

        Spotify::setToken($request->user()->spotifyToken)->pause();

        Cache::forget('jam');

        JamEnded::dispatch();

        return response()->json(status: Response::HTTP_ACCEPTED);
    });
});
