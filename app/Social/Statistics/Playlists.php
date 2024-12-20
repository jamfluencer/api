<?php

namespace App\Social\Statistics;

use App\Playback\Playlist;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Cache;

class Playlists
{
    public function __invoke(): array
    {
        return Cache::flexible(
            'statistics.playlists',
            [
                CarbonInterval::create(hours: 1)->totalSeconds,
                CarbonInterval::create(days: 1)->totalSeconds,
            ],
            fn () => [
                'count' => ($playlists = Playlist::query()->withCount('tracks')->get())->count(),
                'tracks' => [
                    'mean' => round($playlists->avg('tracks_count'), 2),
                    'maximum' => $playlists->max('tracks_count'),
                    'minimum' => $playlists->min('tracks_count'),
                ],
                // TODO Exclude the compilation albums.
                'duration' => [
                    'mean' => CarbonInterval::create(seconds: $playlists->avg(fn (Playlist $playlist) => $playlist->duration) / CarbonInterval::getMillisecondsPerSecond())->cascade()->forHumans(),
                    'maximum' => CarbonInterval::create(seconds: $playlists->max('duration') / CarbonInterval::getMillisecondsPerSecond())->cascade()->forHumans(),
                    'minimum' => CarbonInterval::create(seconds: $playlists->min('duration') / CarbonInterval::getMillisecondsPerSecond())->cascade()->forHumans(),
                ],
            ]);
    }
}
