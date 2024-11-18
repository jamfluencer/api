<?php

use App\Models\User;
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
        $ryanSpotify = $ryan->spotifyAccounts()->sole()->id;
        /** @var PlaylistModel $currentPlaylist */
        $currentPlaylist = PlaylistModel::factory()->create();
        $currentPlaylist->tracks()->sync([
            ($trackOne = TrackModel::factory()->create())->id => ['added_by' => $ryanSpotify],
            ($trackTwo = TrackModel::factory()->create())->id => ['added_by' => $marshall->spotifyAccounts()->sole()->id],
            ($trackThree = TrackModel::factory()->create())->id => ['added_by' => $ryanSpotify],
        ]);
        Spotify::shouldReceive('setToken')->andReturnSelf();
        Spotify::shouldReceive('playlist')->andReturn(new Playlist(
            name: $currentPlaylist->name,
            id: $currentPlaylist->id,
            images: [],
            tracks: $tracks = [
                new Track(
                    name: $trackOne->name,
                    album: null,
                    artists: [],
                    id: $trackOne->id,
                    added_by: $ryanSpotify
                ),
                new Track(
                    name: $trackTwo->name,
                    album: null,
                    artists: [],
                    id: $trackTwo->id,
                    added_by: $ryanSpotify,
                ),
                new Track(
                    name: $trackThree->name,
                    album: null,
                    artists: [],
                    id: $trackThree->id,
                    added_by: $ryanSpotify,
                ),
            ],
            totalTracks: count($tracks),
            next: null,
            url: $currentPlaylist->url,
            snapshot: $currentPlaylist->snapshot
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
