<?php

use App\Models\User;
use App\Playback\Album as AlbumModel;
use App\Playback\Jobs\StorePlaylist;
use App\Playback\Playlist as PlaylistModel;
use App\Playback\Track as TrackModel;
use App\Spotify\Events\JamStarted;
use App\Spotify\Facades\Spotify;
use App\Spotify\Jobs\PollJam;
use App\Spotify\Playlist;
use App\Spotify\Track;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\Fluent\AssertableJson;

describe('V1', function () {
    it('starts the jam', function () {})->todo();

    it('reports properly attributed playlists', function () {
        Cache::put('jam', [
            'user' => User::factory()->withSpotify()->create()->id,
            'playlist' => 'spotify:playlist',
        ]);
        $ryan = User::factory()->withSpotify()->create();
        $marshall = User::factory()->withSpotify()->create();
        $ryanTrack = TrackModel::factory()
            ->hasAttached(PlaylistModel::factory(), ['added_by' => $ryanSpotify = $ryan->spotifyAccounts()->sole()->id])
            ->hasAttached(AlbumModel::factory())
            ->create();
        $marshallTrack = TrackModel::factory()
            ->hasAttached(PlaylistModel::factory(), ['added_by' => $marshall->spotifyAccounts()->sole()->id])
            ->hasAttached(AlbumModel::factory())
            ->create();
        Spotify::shouldReceive('setToken')->andReturnSelf();
        Spotify::shouldReceive('playlist')->andReturn(new Playlist(
            name: 'Playlist',
            id: 'id',
            images: [],
            tracks: $tracks = [
                new Track(
                    name: 'Previously Added by Ryan',
                    album: null,
                    artists: [],
                    id: $ryanTrack->id,
                    added_by: $ryanSpotify
                ),
                new Track(
                    name: 'Previously Added by Marshall',
                    album: null,
                    artists: [],
                    id: $marshallTrack->id,
                    added_by: $ryanSpotify,
                ),
                new Track(
                    name: 'Newly Added by Ryan',
                    album: null,
                    artists: [],
                    id: 'new',
                    added_by: $ryanSpotify,
                ),
            ],
            totalTracks: count($tracks),
            next: 'next',
            url: '',
            snapshot: 'snapshot'
        ));

        $this->get('v1/jam/playlist')
            ->assertJson(fn (AssertableJson $playlist) => $playlist
                ->where('tracks.0.added_by', $ryanSpotify)
                ->where('tracks.1.added_by', $marshall->spotifyAccounts()->sole()->id)
                ->where('tracks.0.added_by', $ryanSpotify)
                ->etc(/* Only testing the attribution here. */));
    });
});
describe('V2', function () {
    it('starts the jam', function () {
        Event::fake();
        Queue::fake();
        Http::fake([
            '*' => Http::response(),
        ]);
        $this
            ->actingAs(User::factory()->withSpotify()->create())
            ->postJson(
                '/v2/jam/start',
                [
                    'playlist' => 'spotify:playlist:abcdef',
                    'jam' => 'https://spotify.link/wxyz',
                ]
            )
            ->assertOk();

        Event::assertDispatched(JamStarted::class);
        Queue::assertPushed(PollJam::class);
        Queue::assertPushed(StorePlaylist::class);
    });
});
