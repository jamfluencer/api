<?php

namespace App\Social\Statistics;

use App\Playback\Playlist;
use Carbon\CarbonInterval;

class Playlists
{
    public function __invoke(): array
    {
        return [
            'count' => ($playlists = Playlist::query()->withCount('tracks')->get())->count(),
            'tracks' => [
                'mean' => round($playlists->avg('tracks_count'), 2),
                'maximum' => $playlists->max('tracks_count'),
                'minimum' => $playlists->min('tracks_count'),
            ],
            'duration' => [
                'mean' => CarbonInterval::create(seconds: $playlists->avg(fn (Playlist $playlist) => $playlist->tracks->avg('duration'))/CarbonInterval::getMillisecondsPerSecond())->forHumans(),
                'maximum' => CarbonInterval::create(seconds:$playlists->max(fn (Playlist $playlist) => $playlist->tracks->max('duration'))/CarbonInterval::getMillisecondsPerSecond())->forHumans(),
                'minimum' => CarbonInterval::create(seconds:$playlists->min(fn (Playlist $playlist) => $playlist->tracks->min('duration'))/CarbonInterval::getMillisecondsPerSecond())->forHumans(),
            ],
        ];
    }
}
