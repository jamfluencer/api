<?php

use App\Http\Middleware\CheckJamMiddleware;
use App\Playback\Playlist;
use App\Playback\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

it('rejects traffic when no Jam is active', function () {
    /** @var JsonResponse $response */
    $response = $this->app->make(CheckJamMiddleware::class)
        ->handle($this->app->make(Request::class), fn (Request $request) => $request);

    expect($response->status())->toBe(503)
        ->and($response->getData()->message)->toBe('NO JAM FOR YOU');
});

it('allows traffic when Jam is active', function () {
    $key = Str::random();
    Cache::put(
        'jam',
        [
            'playlist' => Playlist::factory()
                ->hasAttached(
                    $track = Track::factory()->create(),
                    ['added_by' => Str::random()]
                )
                ->create()->id,
            'user' => Str::random(),
            'currently_playing' => $track->id,
        ]
    );

    /** @var JsonResponse $response */
    expect($this->app->make(CheckJamMiddleware::class)
        ->handle($this->app->make(Request::class), fn (Request $request) => $key))
        ->toBe($key);
});
