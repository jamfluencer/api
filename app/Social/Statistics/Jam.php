<?php

namespace App\Social\Statistics;

use App\Playback\Playlist;
use App\Playback\Track;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Jam
{
    public function __invoke(): array
    {
        return Cache::flexible(
            'statistics.jam',
            [
                CarbonInterval::create(hours: 1)->totalSeconds,
                CarbonInterval::create(days: 1)->totalSeconds,
            ],
            fn () => [
                'playlists' => Playlist::query()->count(),
                'tracks' => Track::query()->count(),
                'duration' => CarbonInterval::create(seconds: Track::query()->sum('duration') / CarbonInterval::getMillisecondsPerSecond())->cascade()->forHumans(),
                'contributors' => DB::table('spotify_playlist_tracks')->distinct('added_by')->count(),
            ]);
    }
}
