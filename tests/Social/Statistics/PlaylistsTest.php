<?php

use App\Playback\Playlist;
use App\Playback\Track;
use App\Social\Statistics\Playlists;
use Carbon\CarbonInterval;
use Illuminate\Support\Arr;

it('calculates the longest playlist', function () {
    $oneMinute = CarbonInterval::getMillisecondsPerSecond() * 60;
    Playlist::factory()
        ->hasAttached(Track::factory()->count(5)->create(['duration' => $oneMinute]))
        ->create();
    Playlist::factory()
        ->hasAttached(Track::factory()->count(10)->create(['duration' => $oneMinute]))
        ->create();
    Playlist::factory()
        ->hasAttached(Track::factory()->count(6)->create(['duration' => $oneMinute]))
        ->create();

    expect($playlists = (new Playlists)())
        ->toHaveKeys([
            'count',
            'tracks',
            'duration',
        ])
        ->and($duration = Arr::get($playlists, 'duration'))
        ->and($duration)
        ->toMatchArray([
            'mean' => '7 minutes',
            'minimum' => '5 minutes',
            'maximum' => '10 minutes',
        ]);
});
