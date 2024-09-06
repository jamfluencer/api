<?php

use App\Models\User;
use App\Playback\Jobs\StorePlaylist;
use App\Playback\Playlist as PlaylistModel;
use App\Playback\Track as TrackModel;
use App\Spotify\Album;
use App\Spotify\Facades\Spotify;
use App\Spotify\Playlist;
use App\Spotify\Track;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

describe('For Non-collaborative Playlists', function () {
    it('logs who added tracks', function () {
        $repeatedTrack = new Track(
            name: $this->faker->name(),
            album: new Album(
                Str::random(),
                $this->faker->name(),
                []
            ),
            artists: [],
            id: Str::random(),
            added_by: $playlistAuthor = fake()->name()
        );
        PlaylistModel::factory()
            ->hasAttached(new TrackModel([
                'id' => $repeatedTrack->id,
                'name' => $repeatedTrack->name,
            ]), ['added_by' => fake()->name()])
            ->create();
        Spotify::shouldReceive('setToken')->once()->andReturnSelf();
        Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
            new Playlist(
                name: $this->faker->name(),
                id: $id,
                images: [],
                tracks: $tracks = [
                    $repeatedTrack,
                    new Track(
                        name: $this->faker->name(),
                        album: new Album(
                            Str::random(),
                            $this->faker->name(),
                            []
                        ),
                        artists: [],
                        id: Str::random(),
                        added_by: $playlistAuthor
                    ),
                    new Track(
                        name: $this->faker->name(),
                        album: new Album(
                            Str::random(),
                            $this->faker->name(),
                            []
                        ),
                        artists: [],
                        id: Str::random(),
                        added_by: $playlistAuthor
                    ),
                ],
                totalTracks: count($tracks),
                next: '',
                url: $this->faker->url(),
                snapshot: Str::random()
            )
        );

        App::make(StorePlaylist::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();

        expect(PlaylistModel::query()->where('id', $id)->sole()->tracks()
            ->wherePivot('added_by', '!=', $playlistAuthor)->exists())
            ->toBeFalse('Track was attributed to incorrect occurrence.');
    });
});

describe('For Collaborative Playlists', function () {
    it('logs who first added tracks', function () {
        PlaylistModel::factory()
            ->hasAttached($repeated = TrackModel::factory()->create(), ['added_by' => $originalGangster = fake()->name()])
            ->create();
        Spotify::shouldReceive('setToken')->once()->andReturnSelf();
        Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
            new Playlist(
                name: $this->faker->name(),
                id: $id,
                images: [],
                tracks: $tracks = [
                    new Track(
                        name: $repeated->name,
                        album: new Album(
                            Str::random(),
                            $this->faker->name(),
                            []
                        ),
                        artists: [],
                        id: $repeated->id,
                        added_by: $ryan = Str::random()
                    ),
                    new Track(
                        name: $this->faker->name(),
                        album: new Album(
                            Str::random(),
                            $this->faker->name(),
                            []
                        ),
                        artists: [],
                        id: Str::random(),
                        added_by: $ryan
                    ),
                    new Track(
                        name: $this->faker->name(),
                        album: new Album(
                            Str::random(),
                            $this->faker->name(),
                            []
                        ),
                        artists: [],
                        id: Str::random(),
                        added_by: $ryan
                    ),
                ],
                totalTracks: count($tracks),
                next: '',
                url: $this->faker->url(),
                snapshot: Str::random(),
                collaborative: false
            )
        );

        App::make(StorePlaylist::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();

        expect(PlaylistModel::query()->where('id', $id)->sole()
            ->tracks()->where('id', $repeated->id)->wherePivot('added_by', $originalGangster)
            ->exists())
            ->toBetrue('Track was attributed to incorrect occurrence.');
    });
});
