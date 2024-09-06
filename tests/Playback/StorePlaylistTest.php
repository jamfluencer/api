<?php

use App\Models\User;
use App\Playback\Artist as ArtistModel;
use App\Playback\Jobs\StorePlaylist as StorePlaylistJob;
use App\Playback\Playlist as PlaylistModel;
use App\Playback\Track as TrackModel;
use App\Spotify\Album;
use App\Spotify\Artist;
use App\Spotify\Facades\Spotify;
use App\Spotify\Playlist;
use App\Spotify\Track;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

it('stores playlist', function () {
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $name = $this->faker->name(),
            id: $id,
            images: [],
            tracks: [],
            totalTracks: 0,
            next: '',
            url: $url = $this->faker->url(),
            snapshot: $snapshot = Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();

    expect(PlaylistModel::query()->where([
        'id' => $id,
        'name' => $name,
        'snapshot' => $snapshot,
        'url' => $url,
    ])->exists())->toBeTrue();
});

it('associates the tracks', function () {
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $this->faker->name(),
            id: $id,
            images: [],
            tracks: $tracks = [
                new Track(
                    $this->faker->name(),
                    new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    [],
                    Str::random()
                ),
                new Track(
                    $this->faker->name(),
                    new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    [],
                    Str::random()
                ),
                new Track(
                    $this->faker->name(),
                    new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    [],
                    Str::random()
                ),
            ],
            totalTracks: count($tracks),
            next: '',
            url: $this->faker->url(),
            snapshot: Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();

    expect(PlaylistModel::query()->sole()
        ->tracks)->toHaveCount(count($tracks));

});

it('logs who added tracks', function () {
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $this->faker->name(),
            id: $id,
            images: [],
            tracks: $tracks = [
                new Track(
                    name: $this->faker->name(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [],
                    id: Str::random(),
                    added_by: Str::random()
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
                    added_by: Str::random()
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
                    added_by: Str::random()
                ),
            ],
            totalTracks: count($tracks),
            next: '',
            url: $this->faker->url(),
            snapshot: Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();

    expect(PlaylistModel::query()->sole()->tracks()->wherePivotNull('added_by')->exists())->toBeFalse();
});

it('handles repeated tracks', function () {
    /** @var PlaylistModel $existingPlaylist */
    $existingPlaylist = PlaylistModel::factory()
        ->afterCreating(fn (PlaylistModel $playlist) => TrackModel::factory()->hasAttached($playlist)->create())
        ->create();
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $this->faker->name(),
            id: $id,
            images: [],
            tracks: $tracks = [
                new Track(
                    name: $this->faker->name(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [],
                    id: $existingPlaylist->tracks->first()->id,
                    added_by: Str::random()
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
                    added_by: Str::random()
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
                    added_by: Str::random()
                ),
            ],
            totalTracks: count($tracks),
            next: '',
            url: $this->faker->url(),
            snapshot: Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();

    expect(PlaylistModel::query()->count())->toBe(2, 'Unexpected number of Playlists.')
        ->and($existingPlaylist->tracks->first()->playlists()->count())->toBe(2, 'Track associated with incorrect number of Playlists.');
});

it('does not add all tracks again', function () {
    Spotify::shouldReceive('setToken')->twice()->andReturnSelf();
    Spotify::shouldReceive('playlist')->twice()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $this->faker->name(),
            id: $id,
            images: [],
            tracks: $tracks = [
                new Track(
                    name: $this->faker->name(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [],
                    id: Str::random(),
                    added_by: Str::random()
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
                    added_by: Str::random()
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
                    added_by: Str::random()
                ),
            ],
            totalTracks: count($tracks),
            next: '',
            url: $this->faker->url(),
            snapshot: Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();
    App::make(StorePlaylistJob::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();

    expect(PlaylistModel::query()->sole()->tracks()->count())->toBe(3);
});

it('stores track names', function () {
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $this->faker->name(),
            id: $id,
            images: [],
            tracks: $tracks = [
                new Track(
                    name: $this->faker->name(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [],
                    id: Str::random(),
                    added_by: Str::random()
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
                    added_by: Str::random()
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
                    added_by: Str::random()
                ),
            ],
            totalTracks: count($tracks),
            next: '',
            url: $this->faker->url(),
            snapshot: Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();

    expect(TrackModel::query()->whereNull('name')->count())->toBe(0);
});

it('stores artists', function () {
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $this->faker->name(),
            id: $id,
            images: [],
            tracks: $tracks = [
                new Track(
                    name: $this->faker->name(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [
                        new Artist(
                            id: Str::random(),
                            name: fake()->name(),
                            uri: fake()->url(),
                        ),
                    ],
                    id: Str::random(),
                    added_by: Str::random()
                ),
                new Track(
                    name: $this->faker->name(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [
                        new Artist(...[
                            'name' => fake()->name(),
                            'id' => Str::random(),
                            'uri' => fake()->url(),
                        ]),
                        new Artist(
                            id: Str::random(),
                            name: fake()->name(),
                            uri: fake()->url(),
                        ),
                    ],
                    id: Str::random(),
                    added_by: Str::random()
                ),
            ],
            totalTracks: count($tracks),
            next: '',
            url: $this->faker->url(),
            snapshot: Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['user' => User::factory()->withSpotify()->create(), 'id' => $id])->handle();

    expect(ArtistModel::query()->count())->toBe(3);
});
