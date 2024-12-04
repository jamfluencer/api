<?php

use App\Playback\Track;
use App\Social\Statistics\Tracks;
use Carbon\CarbonInterval;

it('handles having zero tracks', function () {
    expect($output = (new Tracks)())->toHaveKeys(['duration'])
        ->and($output['duration'])->toHaveKeys(['mean', 'maximum', 'minimum'])
        ->and($output['duration']['mean'])->toBe(CarbonInterval::create(seconds: 0)->forHumans());
});

it('displays the mean duration', function () {
    Track::factory()
        ->count(10)
        ->create();

    expect($output = (new Tracks)())->toHaveKeys(['duration'])
        ->and($output['duration'])->toHaveKeys(['mean', 'maximum', 'minimum'])
        ->and($output['duration']['mean'] ?? '')->toMatch('/(?:\d+ \w+?)+/');
});
