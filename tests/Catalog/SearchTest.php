<?php

use App\Models\User;
use App\Playback\Playlist;
use App\Playback\Track;

describe('Searching by track', function () {
    it('allows searching by ID', function () {
        $track = Track::factory()
            ->hasAttached(Playlist::factory(), ['added_by' => User::factory()->create()->id])
            ->create();
        Track::factory()
            ->hasAttached(Playlist::factory(), ['added_by' => User::factory()->create()->id])
            ->create();

        $this->getJson("v1/catalog/search?track={$track->id}")
            ->assertSuccessful()
            ->assertJson([
                $track->playlists->sole()->id => $track->playlists->sole()->name,
            ]);
    });

    it('allows searching by title', function () {
        $track = Track::factory()
            ->hasAttached(Playlist::factory(), ['added_by' => User::factory()->create()->id])
            ->create();

        $this->getJson("v1/catalog/search?track={$track->title}")
            ->assertSuccessful();
    })->skip('Requires storing more Track information.');
});

describe('Searching by artist', function () {
    it('allows searching by name', function () {})->skip('Requires storing more Track information.');
    it('allows searching by ID', function () {})->skip('Requires storing more Track information.');
});

describe('Searching by album', function () {
    it('allows searching by name', function () {})->skip('Requires storing more Track information.');
    it('allows searching by ID', function () {})->skip('Requires storing more Track information.');
});
