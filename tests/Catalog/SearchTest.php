<?php

use App\Models\User;
use App\Playback\Artist;
use App\Playback\Playlist;
use App\Playback\Track;
use Illuminate\Testing\Fluent\AssertableJson;

describe('Searching by track', function () {
    it('allows searching by ID', function () {
        $track = Track::factory()
            ->hasAttached(Playlist::factory(), ['added_by' => User::factory()->create()->id])
            ->create();
        Track::factory()
            ->hasAttached(Playlist::factory(), ['added_by' => User::factory()->create()->id])
            ->create();

        $this->getJson("v1/catalog/search?term={$track->id}")
            ->assertSuccessful()
            ->assertJson([
                'tracks' => [
                    $track->toArray(),
                ],
            ]);
    });

    it('allows searching by title', function () {
        $track = Track::factory()
            ->hasAttached(Playlist::factory(), ['added_by' => User::factory()->create()->id])
            ->create();

        $this->getJson("v1/catalog/search?term={$track->name}")
            ->assertSuccessful()
            ->assertJson([
                'tracks' => [
                    $track->toArray(),
                ],
            ]);
    });
});

describe('Searching by artist', function () {
    it('allows searching by name', function () {
        $track = Track::factory()
            ->hasAttached(Playlist::factory(), ['added_by' => User::factory()->create()->id])
            ->hasAttached(Artist::factory())
            ->hasAttached(Artist::factory())
            ->create();

        $this->getJson("v1/catalog/search?term={$track->artists->first()->name}")
            ->assertSuccessful()
            ->assertJson(fn (AssertableJson $response) => $response
                ->has(
                    'artists',
                    1,
                    fn (AssertableJson $artists) => $artists
                        ->where('id', $track->artists->first()->id)
                        ->etc(/* It can be assumed that a matching ID identifies the entity. */)
                ));
    });
    it('allows searching by ID', function () {
        $track = Track::factory()
            ->hasAttached(Playlist::factory(), ['added_by' => User::factory()->create()->id])
            ->hasAttached(Artist::factory())
            ->create();

        $this->getJson("v1/catalog/search?term={$track->artists->first()->id}")
            ->assertSuccessful()
            ->assertJson(fn (AssertableJson $response) => $response
                ->has(
                    'artists',
                    1,
                    fn (AssertableJson $artists) => $artists
                        ->where('id', $track->artists->first()->id)
                        ->etc(/* It can be assumed that a matching ID identifies the entity. */)
                ));
    });
});

describe('Searching by album', function () {
    it('allows searching by name', function () {})->skip('Requires storing more Track information.');
    it('allows searching by ID', function () {})->skip('Requires storing more Track information.');
});
