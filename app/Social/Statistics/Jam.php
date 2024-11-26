<?php

namespace App\Social\Statistics;

use App\Playback\Playlist;
use App\Playback\Track;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;

class Jam
{
    public function __invoke(): array
    {
        return [
            'playlists' => Playlist::query()->count(),
            'tracks' => Track::query()->count(),
            'duration' => CarbonInterval::create(seconds: Track::query()->sum('duration') / CarbonInterval::getMillisecondsPerSecond())->forHumans(),
            'contributors' => DB::table('spotify_playlist_tracks')->distinct('added_by')->count(),
        ];
    }
}
