<?php

use App\Models\Kudos;
use App\Models\User;
use App\Playback\Playlist;
use App\Playback\SpotifyAccount;
use App\Playback\Track;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\Fluent\AssertableJson;

it('allows for specifying the track', function () {
    $track = Track::factory()
        ->hasAttached(
            $playlist = Playlist::factory()->create(),
            ['added_by' => ($account = SpotifyAccount::factory()->create())->id]
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

    expect($account->loadCount('kudos')->kudos_count)->toBe(1)
        ->and($account->load('kudos')->kudos->first()->track->id)->toBe($track->id);
});

it('returns not found without a track', function () {
    $this->postJson('/v1/jam/kudos')
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
                ->hasAttached(
                    $track = Track::factory()->create(),
                    ['added_by' => $account = SpotifyAccount::factory()->create()->id]
                )
                ->create()->id,
            'user' => $account,
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
                'forSpotifyAccount',
                fn (Builder $whereAccount) => $whereAccount->where('id', $account)
            )
            ->exists()
    )->toBeTrue();
});

it('allows specifying a playlist', function () {
    $this->postJson(
        '/v1/jam/kudos',
        [
            'track' => $track = Track::factory()
                ->hasAttached(
                    $playlist = Playlist::factory()->create(),
                    ['added_by' => $account = SpotifyAccount::factory()->create()->id]
                )
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
                'forSpotifyAccount',
                fn (Builder $whereAccount) => $whereAccount->where('id', $account)
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
    Playlist::factory()
        ->count(2)
        ->hasAttached($track = Track::factory()->create(), ['added_by' => SpotifyAccount::factory()->create()->id])
        ->create();
    Cache::put(
        'jam',
        [
            'playlist' => Playlist::factory()->create()->id,
        ]
    );

    $this->postJson('/v1/jam/kudos', ['track' => $track->id])
        ->assertAccepted();

    expect(Kudos::query()->where([
        'track_id' => $track->id,
        'playlist_id' => $track->first_occurrence->id,
        'for_spotify_account_id' => $track->first_occurrence?->pivot->added_by,
    ])->exists())->toBeTrue();
});

it('stores the sender', function () {
    $this
        ->actingAs($sender = User::factory()->create())
        ->postJson(
            'v1/jam/kudos',
            [
                'track' => Track::factory()
                    ->hasAttached(Playlist::factory()->create(), ['added_by' => SpotifyAccount::factory()->create()->id])
                    ->create()->id,
            ]
        )
        ->assertAccepted();

    expect(Kudos::query()->where(['from_user_id' => $sender->id])->exists())->toBeTrue();
});

it('does not allow giving oneself kudos', function () {
    $this->actingAs($cheater = User::factory()->withSpotify()->create())
        ->postJson(
            '/v1/jam/kudos',
            [
                'track' => Track::factory()
                    ->hasAttached(
                        Playlist::factory()->create(),
                        ['added_by' => $cheater->spotifyAccounts->first()->display_name]
                    )
                    ->create()->id,
            ]
        )
        ->assertPaymentRequired();

    expect(Kudos::query()->count())->toBe(0);
});

it('throttles kudos', function () {
    $this->freezeTime();
    $this->postJson('v1/jam/kudos')->assertNotFound();
    $this->postJson('v1/jam/kudos')->assertTooManyRequests();
    $this->travel(2)->minutes();
    $this->postJson('v1/jam/kudos')->assertNotFound();
});