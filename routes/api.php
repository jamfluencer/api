<?php

use App\Models\User;
use App\Playback\SpotifyAccount;
use App\Playback\SpotifyToken;
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
    Route::get('/spotify/auth', fn () => response()
        ->json(['url' => Spotify::authUrl('https://localhost:3000/auth/spotify/callback')]));

    Route::post('/spotify/auth', function (Request $request) {
        $request->validate(['code' => ['required', 'string']]);
        $token = Spotify::accessToken($request->input('code'));
        /** @var SpotifyToken $token */
        $token = SpotifyToken::query()->make([
            'token' => $token->token,
            'scope' => $token->scopes->implode(','),
            'expires_at' => $token->expiresAt,
            'refresh' => $token->refresh,
        ]);
        $account = Spotify::setToken($token)->profile();
        tap(
            // TODO It should be first of any accounts, not just the user's
            $request->user()->spotifyAccounts()->firstOrCreate(
                [
                    'id' => $account->id,

                ],
                [
                    'display_name' => $account->display_name,
                    'country' => $account->country,
                ]
            ),
            fn (SpotifyAccount $account) => $account->token()->delete())
            ->token()->save($token);

        return response()->noContent();
    });

    Route::get('/me', fn (Request $request) => $request->user());

    Route::get('/spotify/player/track', function (): JsonResponse {
        if (Cache::has('jam') === false) {
            return response()->json(['message' => 'NO JAM FOR YOU'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        $track = Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', []), 'user'))->spotifyToken)
            ->currentlyPlaying();

        return response()->json($track);
    });

    Route::get('/spotify/player/queue', function (): JsonResponse {
        if (Cache::has('jam') === false) {
            return response()->json(['message' => 'NO JAM FOR YOU'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
        $queue = Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', []), 'user'))->spotifyToken)
            ->queue();

        return response()->json($queue);
    });

    Route::get('/spotify/playlists/{id}', function (Request $request, string $id): JsonResponse {
        try {
            $playlist = Spotify::setToken($request->user()?->spotifyToken)
                ->playlist($id, $request->boolean('complete'));
        } catch (TypeError) {
            throw new RuntimeException('No Spotify authorization for user.');
        }

        return response()->json($playlist);
    });

    Route::put('/jam/start/{playlist?}', function (Request $request, ?string $playlist = null): JsonResponse {
        if ($request->user()->spotifyToken === null) {
            return response()->json(['message' => 'No valid Spotify token for authenticated user.'], Response::HTTP_UNAUTHORIZED);
        }

        $spotify = Spotify::setToken($request->user()->spotifyToken);
        $track = $spotify->play($playlist);

        Cache::put(
            'jam',
            [
                'user' => $request->user()->id,
                'playlist' => $playlist,
            ],
            60 * 60 * 8
        );

        JamStarted::dispatch();

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

    Route::get(
        '/jam/playlist',
        function () {
        if (Cache::has('jam') === false) {
            return response()->json(['message' => 'No one be jammin\''], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return redirect('/spotify/playlists/' . Arr::get(Cache::get('jam', []), 'playlist'));
    }
    )->withoutMiddleware(['auth:sanctum']);

    Route::get('/jam/queue', function () {
        if (Cache::has('jam') === false) {
            return response()->json(['message' => 'No one be jammin\''], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json(Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', []), 'user'))->spotifyToken)
            ->queue());
    })->withoutMiddleware(['auth:sanctum']);
});
