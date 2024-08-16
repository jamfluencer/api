<?php

use App\Models\User;
use App\Playback\Playlist as PlaylistModel;
use App\Spotify\Facades\Spotify;
use App\Spotify\Playlist;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

it('loads a given playlist', function () {
    $playlistId = Str::random();
    Spotify::shouldReceive('setToken')
        ->once()
        ->andReturnSelf();
    Spotify::shouldReceive('playlist')
        ->with($playlistId, true)
        ->andReturn(
            new Playlist(
                name: $name = $this->faker->name(),
                id: $playlistId,
                images: [],
                tracks: [],
                totalTracks: 0,
                next: '',
                url: $this->faker->url(),
                snapshot: Str::random()
            )
        );

    Artisan::call('data:load-playlist', [
        'as' => User::factory()
            ->withSpotify()
            ->create()
            ->email,
        'playlist' => $playlistId,
    ]);

    expect(PlaylistModel::query()
        ->where('id', $playlistId)
        ->exists())
        ->toBeTrue();
});
