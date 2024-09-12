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

describe('For collaborative Playlists', function () {
    it('logs who added tracks', function () {
        PlaylistModel::factory()
            ->hasAttached($repeatedTrack = TrackModel::factory()->create(), ['added_by' => $originalContributor = fake()->name()])
            ->create();
        Spotify::shouldReceive('setToken')->once()->andReturnSelf();
        Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
            new Playlist(
                name: $this->faker->name(),
                id: $id,
                images: [],
                tracks: $tracks = [
                    new Track(
                        name: $repeatedTrack->name,
                        album: new Album(
                            Str::random(),
                            $this->faker->name(),
                            []
                        ),
                        artists: [],
                        id: $repeatedTrack->id,
                        added_by: fake()->name()
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
                        added_by: fake()->name()
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
                        added_by: fake()->name()
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
            ->wherePivot('added_by', $originalContributor)->exists())
            ->toBeFalse('Track was attributed to incorrect occurrence.');
    });
});

describe('For Non-collaborative Playlists', function () {
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

it('detaches previous tracks for update', function () {
    $replaced = PlaylistModel::factory()
        ->hasAttached(TrackModel::factory()->count(10), fn () => ['added_by' => 'spacey_prince'])
        ->create();
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with("{$replaced->id}", true)->andReturn(
        new Playlist(
            name: $this->faker->name(),
            id: $replaced->id,
            images: [],
            tracks: $tracks = [
                new Track(
                    name: fake()->word(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [],
                    id: Str::random(),
                    uri: fake()->url,
                    added_by: fake()->name(),
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
                    uri: fake()->url,
                    added_by: fake()->name(),
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
                    uri: fake()->url,
                    added_by: fake()->name(),
                ),
            ],
            totalTracks: count($tracks),
            next: '',
            url: $this->faker->url(),
            snapshot: Str::random(),
            collaborative: false
        )
    );

    App::make(StorePlaylist::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $replaced->id])->handle();

    expect($replaced->tracks()->count())->toBe(3);
});

it('attributes the song when reporting', function () {})->todo();
