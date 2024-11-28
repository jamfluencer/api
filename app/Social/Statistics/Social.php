<?php

namespace App\Social\Statistics;

use App\Playback\Playlist;
use App\Playback\SpotifyAccount;
use App\Playback\Track;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Cache;

class Social
{
    public function __invoke()
    {
        return Cache::flexible(
            'statistics.social',
            [
                CarbonInterval::create(hours: 1)->totalSeconds,
                CarbonInterval::create(days: 1)->totalSeconds,
            ],
            fn () => [
                'track' => [
                    'details' => ($mostAppreciated = Track::query()
                        ->withCount('kudos')
                        ->with([
                            'albums',
                            'artists',
                        ])
                        ->orderBy('kudos_count', 'desc')->first())
                        ?->toArray(),
                    'kudos' => $mostAppreciated->kudos_count,
                ],
                'contributor' => [
                    'details' => ($contributor = SpotifyAccount::query()
                        ->withCount('kudos')->orderBy('kudos_count', 'desc')
                        ->first())
                        ->mappedDisplayName,
                    'kudos' => $contributor->kudos_count,
                ],
                'playlist' => [
                    'details' => ($playlist = Playlist::query()->get()
                        ->sort(fn (Playlist $a, Playlist $b) => $b->kudos <=> $a->kudos)
                        ->first()->setVisible(['name', 'url']))->toArray(),
                    'kudos' => $playlist->kudos,
                ],
            ]
        );
    }
}
