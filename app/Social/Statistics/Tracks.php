<?php

namespace App\Social\Statistics;

use App\Playback\Playlist;
use App\Playback\Track;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Cache;

class Tracks
{
    public function __invoke()
    {
        return Cache::flexible(
            'statistics.tracks',
            [
                CarbonInterval::create(hours: 1)->totalSeconds,
                CarbonInterval::create(days: 1)->totalSeconds,
            ],
            fn () => [
                'count' => Track::query()->count(),
                'duration' => [
                    'mean' => Track::query()->avg('duration'),
                    'longest' => Track::query()->orderByDesc('duration')->first()->setVisible(['name', 'url', 'duration'])->toArray(),
                    'shortest' => Track::query()->orderBy('duration')->first()->setVisible(['name', 'url', 'duration'])->toArray(),
                ],
                'occurrence' => [
                    'details' => ($track = Track::query()->withCount('playlists')->orderByDesc('playlists_count')->first())->setVisible(['name', 'url'])->toArray(),
                    'playlists' => $track->playlists->each(fn (Playlist $playlist) => $playlist->setVisible(['name', 'url']))->toArray(),
                ],
            ]
        );
    }
}
