<?php

use App\Models\Kudos;
use App\Models\User;
use App\Playback\Playlist;
use App\Playback\Track;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\Fluent\AssertableJson;

it('allows for specifying the track', function () {
    $track = Track::factory()
        ->hasAttached(
            $playlist = Playlist::factory()->create(),
            ['added_by' => ($user = User::factory()->create())->id]
        )
        ->create();
    Cache::put('jam', ['playlist' => $playlist->id]);

    expect($track->exists)->toBeTrue();

    $this->postJson(
        '/v1/jam/kudos',
        [
            'track' => $track->id,
        ]
    )
        ->assertAccepted();

    expect($user->loadCount('kudos')->kudos_count)->toBe(1)
        ->and($user->load('kudos')->kudos->first()->track->id)->toBe($track->id);
});

it('returns not found without a track', function () {
    $this->postJson(
        '/v1/jam/kudos',
        [
            'for' => User::factory()->create()->id,
        ]
    )
        ->assertNotFound();
    expect(Kudos::query()->count())->toBe(0);
});

it('returns not found without a user', function () {
    $this->postJson(
        '/v1/jam/kudos',
        [
            'track' => Track::factory()
                ->hasAttached(Playlist::factory()->create())->create()->id,
        ]
    )
        ->assertNotFound();
    expect(Kudos::query()->count())->toBe(0);
});

it('finds necessary information from cache', function () {
    Cache::put(
        'jam',
        [
            'playlist' => $playlist = Playlist::factory()
                ->hasAttached($track = Track::factory()->create(), ['added_by' => $user = User::factory()->create()->id])
                ->create()->id,
            'user' => $user,
            'currently_playing' => $track->id,
        ]
    );

    $this->postJson('/v1/jam/kudos')
        ->assertAccepted();

    expect(
        Kudos::query()
            ->whereHas(
                'track',
                fn (Builder $whereTrack) => $whereTrack->where('id', $track->id)
            )
            ->whereHas(
                'playlist',
                fn (Builder $wherePlaylist) => $wherePlaylist->where('id', $playlist)
            )
            ->whereHas(
                'for',
                fn (Builder $whereUser) => $whereUser->where('id', $user)
            )
            ->exists()
    )->toBeTrue();
});

it('allows specifying a playlist', function () {
    $this->postJson(
        '/v1/jam/kudos',
        [
            'track' => $track = Track::factory()
                ->hasAttached($playlist = Playlist::factory()->create(), ['added_by' => $user = User::factory()->create()->id])
                ->create()
                ->id,
            'playlist' => $playlist->id,
        ]
    )
        ->assertAccepted();
    expect(
        Kudos::query()
            ->whereHas(
                'track',
                fn (Builder $whereTrack) => $whereTrack->where('id', $track)
            )
            ->whereHas(
                'playlist',
                fn (Builder $wherePlaylist) => $wherePlaylist->where('id', $playlist->id)
            )
            ->whereHas(
                'for',
                fn (Builder $whereUser) => $whereUser->where('id', $user)
            )
            ->exists()
    )->toBeTrue();
});

it('returns not found when track is not in playlist', function () {
    $this->postJson(
        '/v1/jam/kudos',
        [
            'track' => Track::factory()->create()->id,
            'playlist' => Playlist::factory()->create()->id,
        ]
    )
        ->assertUnprocessable()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('errors', fn (AssertableJson $errors) => $errors
                ->has('track'))
            ->has('message')
        );
});

it('defaults to first occurrence', function () {
    // TODO If the track exists but is not in the current playlist.
})
    ->todo();

it('stores the sender', function () {})
    ->todo();

it('throttles kudos', function () {})
    ->todo();
