<?php

use App\Models\User;
use App\Spotify\Album;
use App\Spotify\Context;
use App\Spotify\CurrentlyPlayingTrack;
use App\Spotify\Facades\Spotify;
use App\Spotify\Track;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\Fluent\AssertableJson;

it('proxies track information', function () {
    Cache::put('jam', [
        'user' => User::factory()->withSpotify()->create()->id,
    ]);
    Spotify::shouldReceive('setToken')->andReturnSelf();
    Spotify::shouldReceive('currentlyPlaying')->andReturn(new CurrentlyPlayingTrack(
        item: new Track(
            name: 'track',
            album: new Album(
                id: '1',
                name: 'album',
                uri: ''
            ),
            artists: [],
            id: '1',
            duration_ms: 0

        ),
        context: new Context(
            '',
            '',
            [],
            ''
        )
    ));
    $this
        ->actingAs(User::factory()->create())
        ->get('v1/spotify/player/track')
        ->assertJson(fn (AssertableJson $track) => $track
            ->where('name', 'track')
            ->has('album', fn (AssertableJson $album) => $album
                ->where('name', 'album')
                ->where('uri', '')
                ->where('id', '1')
                ->where('images', [])
                ->where('external_urls', []))
            ->where('id', '1')
            ->where('artists', [])
            ->where('url', null)
            ->where('duration_ms', 0)
            ->where('added_by', null));
});
