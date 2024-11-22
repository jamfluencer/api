<?php

use App\Catalog\Requests\Search;
use App\Http\Middleware\CheckJamMiddleware;
use App\Models\User;
use App\Playback\Album;
use App\Playback\Artist;
use App\Playback\AttributedPlaylist;
use App\Playback\Jobs\StorePlaylist;
use App\Playback\Requests\Jam\Start;
use App\Playback\SpotifyAccount;
use App\Playback\SpotifyToken;
use App\Playback\Track;
use App\Social\Events\Kudos as KudosEvent;
use App\Social\Kudos;
use App\Social\Requests\Kudos\Store;
use App\Spotify\Events\JamEnded;
use App\Spotify\Events\JamStarted;
use App\Spotify\Facades\Spotify;
use App\Spotify\Jobs\ImportUser;
use App\Spotify\Jobs\PollJam;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/spotify/auth', fn () => response()
        ->json(['url' => Spotify::authUrl(config('spotify.redirect_uri'))]));

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
        $account = Spotify::setToken($token->forSpotify())->profile();
        tap(
            SpotifyAccount::query()->updateOrCreate(
                [
                    'id' => $account->id,

                ],
                [
                    'display_name' => $account->display_name,
                    'country' => $account->country,
                    'user_id' => $request->user()->id,
                ]
            ),
            fn (SpotifyAccount $account) => $account->token()->delete())
            ->token()->save($token);

        return response()->noContent();
    });

    Route::get('/me', fn (Request $request) => $request->user());

    Route::get('/spotify/player/track', function (): JsonResponse {
        $item = Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', fn () => []), 'user'))->spotifyToken->forSpotify())
            ->currentlyPlaying();

        return response()->json($item->item);
    })->middleware(CheckJamMiddleware::class);

    Route::get('/spotify/player/queue', function (): JsonResponse {
        $queue = Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', fn () => []), 'user'))->spotifyToken->forSpotify())
            ->queue();

        return response()->json($queue);
    })->middleware(CheckJamMiddleware::class);

    Route::get('/spotify/playlists/{id}', function (Request $request, string $id): JsonResponse {
        try {
            $playlist = Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', fn () => []), 'user'))->spotifyToken->forSpotify())
                ->playlist($id, $request->boolean('complete'));
        } catch (TypeError) {
            throw new RuntimeException('No Spotify authorization for user.');
        }

        return response()->json($playlist);
    })->withoutMiddleware(['auth:sanctum'])->middleware(CheckJamMiddleware::class);

    Route::put('/jam/start/{playlist?}', function (Request $request, ?string $playlist = null): JsonResponse {
        if ($request->user()->spotifyToken === null) {
            return response()->json(['message' => 'No valid Spotify token for authenticated user.'], Response::HTTP_UNAUTHORIZED);
        }

        $spotify = Spotify::setToken($request->user()->spotifyToken->forSpotify());
        try {
            $track = $spotify->play($playlist);
        } catch (RequestException $exception) {
            return response()->json($exception->response->json(), $exception->getCode());
        }

        Cache::put(
            'jam',
            [
                'user' => $request->user()->id,
                'playlist' => $playlist,
            ],
            60 * 60 * 8
        );

        JamStarted::dispatch(null);
        PollJam::dispatchAfterResponse();

        return response()->json($track);
    });

    Route::put('/jam/stop', function (Request $request): JsonResponse {
        if ($request->user()->spotifyToken === null) {
            return response()->json(['message' => 'No valid Spotify token for authenticated user.'], Response::HTTP_UNAUTHORIZED);
        }

        Spotify::setToken($request->user()->spotifyToken->forSpotify())->pause();

        Cache::forget('jam');

        JamEnded::dispatch();

        return response()->json(status: Response::HTTP_ACCEPTED);
    });

    Route::get(
        '/jam/playlist',
        function () {
            return response()->json(
                AttributedPlaylist::attribution(
                    Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', fn () => []), 'user'))->spotifyToken->forSpotify())
                        ->playlist(Str::afterLast(Arr::get(Cache::get('jam', fn () => []), 'playlist'), ':'), true)
                )
            );
        }
    )->withoutMiddleware(['auth:sanctum'])->middleware(CheckJamMiddleware::class);

    Route::get('/jam/queue', function () {
        return response()->json(Spotify::setToken(User::query()->find(Arr::get(Cache::get('jam', fn () => []), 'user'))->spotifyToken->forSpotify())
            ->queue());
    })->withoutMiddleware(['auth:sanctum'])->middleware(CheckJamMiddleware::class);
});

Route::prefix('v1')->group(function () {
    Route::post('/jam/kudos', function (Store $request): JsonResponse {
        $kudos = Kudos::query()->make([
            'track_id' => ($track = Track::query()
                ->firstOrCreate([
                    'id' => $request->validated('track', Arr::get(Cache::get('jam', fn () => []), 'currently_playing')),
                ]))
                ?->id,
            'playlist_id' => ($playlist = $track
                ?->playlists()
                ?->find($request->validated('playlist', Arr::get(Cache::get('jam', fn () => []), 'playlist')))
                ?? $track?->first_occurrence
            )
                ?->id,
            'for_spotify_account_id' => $playlist?->pivot?->added_by,
            'from_user_id' => $request->user()?->id,
        ]);

        if ($request->user()?->spotifyAccounts()->where('id', $kudos->for_spotify_account_id)->exists()) {
            return response()->json(['message' => 'Good try though!'], Response::HTTP_PAYMENT_REQUIRED);
        }

        if (
            $kudos->for_spotify_account_id !== null
            && SpotifyAccount::query()->where('id', $kudos->for_spotify_account_id)->doesntExist()
        ) {
            ImportUser::dispatch($kudos->for_spotify_account_id);
        }

        $kudos->save();

        KudosEvent::dispatch($kudos);

        return response()->json(status: Response::HTTP_ACCEPTED);
    })->middleware(['throttle:kudos']);

    Route::prefix('catalog')->group(function () {
        Route::get('search', function (Search $request): JsonResponse {
            $term = Str::lower($request->validated('term'));

            return response()->json(array_filter([
                'tracks' => Track::query()
                    ->where('id', $request->validated('term'))
                    ->orWhereLike(DB::raw('lower(name)'), "%{$term}%")
                    ->with(['playlists', 'artists'])
                    ->get()
                    ->toArray(),
                'albums' => Album::query()
                    ->where('id', $request->validated('term'))
                    ->orWhereLike(DB::raw('lower(name)'), "%{$term}%")
                    ->with(['tracks.playlists', 'images'])
                    ->get()
                    ->toArray(),
                'artists' => Artist::query()
                    ->where('id', $request->validated('term'))
                    ->orWhereLike(DB::raw('lower(name)'), "%{$term}%")
                    ->with(['tracks.playlists'])
                    ->get()
                    ->toArray(),
            ]));
        });
    });
});

Route::prefix('v2')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/jam/start', function (Start $request): JsonResponse {
        if ($request->user()->spotifyToken === null) {
            return response()->json(['message' => 'No valid Spotify token for authenticated user.'], Response::HTTP_UNAUTHORIZED);
        }

        Cache::put(
            $request->validated('jam'),
            [
                'user' => $request->user()->id,
                'playlist' => $request->validated('playlist'),
            ],
            60 * 60 * 8
        );
        Cache::put(
            'jam',
            [
                'user' => $request->user()->id,
                'playlist' => $request->validated('playlist'),
            ],
            60 * 60 * 8
        );

        Http::timeout(3)
            ->post(
                'https://hooks.slack.com/triggers/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',
                [
                    'date' => now()->format('d-m-Y'),
                    'playlist_url' => "https://open.spotify.com/user/spotify/playlist/{$request->validated('playlist')}",
                    'jam_url' => $request->validated('jam'),
                ]
            );

        JamStarted::dispatch($request->validated('jam'));

        StorePlaylist::dispatchAfterResponse($request->user(), $request->validated('playlist'));
        PollJam::dispatchAfterResponse();

        return response()->json();
    });
});
