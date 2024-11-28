<?php

namespace App\Social\Statistics;

use App\Models\User as UserModel;
use App\Playback\Playlist;
use App\Playback\Track;
use App\Social\Kudos;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class User
{
    public function __invoke(UserModel $user): array
    {
        return Cache::flexible(
            "statistics.user.{$user->id}",
            [
                CarbonInterval::create(hours: 1)->totalSeconds,
                CarbonInterval::create(days: 1)->totalSeconds,
            ],
            fn () => [
                'social' => [
                    'kudos' => Kudos::query()
                        ->whereHas('forSpotifyAccount', fn (Builder $spotifyAccountBuilder) => $spotifyAccountBuilder
                            ->whereHas('user', fn (Builder $userBuilder) => $userBuilder->where('id', $user->id)))
                        ->count(),
                    'tracks' => [
                        'most-appreciated' => [
                            'details' => ($mostAppreciated = Track::query()
                                ->withCount('kudos')
                                ->whereHas('playlists', fn (Builder $playlistBuilder) => $playlistBuilder
                                    ->whereIn('spotify_playlist_tracks.added_by', $user->spotifyAccounts->pluck('id'))
                                )
                                ->with([
                                    'albums',
                                    'artists',
                                ])
                                ->orderBy('kudos_count', 'desc')->first())
                                ?->toArray(),
                            'kudos' => $mostAppreciated->kudos_count,
                        ],
                        'mean' => round(Track::query()
                        ->whereHas(
                            'playlists', fn (Builder $playlistBuilder) => $playlistBuilder
                                ->whereIn('spotify_playlist_tracks.added_by', $user->spotifyAccounts->pluck('id'))
                        )
                        ->withCount('kudos')
                        ->get('kudos_count')
                        ->avg('kudos_count'), 2),
                    ],
                ],
                'jam' => [
                'tracks' => [
                    'count' => [
                        'percentage' => round((($userTracks = Track::query()
                            ->whereHas(
                                'playlists', fn (Builder $playlistBuilder) => $playlistBuilder
                                    ->whereIn('spotify_playlist_tracks.added_by',
                                        $user->spotifyAccounts->pluck('id'))
                            )->count()) / Track::query()->count()) * 100, 2),
                        'total' => $userTracks,
                    ],
                    'duration' => [
                        'percentage' => round(
                            (($userDuration = CarbonInterval::create(seconds: Track::query()
                                ->whereHas(
                                    'playlists',
                                    fn (Builder $playlistBuilder) => $playlistBuilder
                                        ->whereIn('spotify_playlist_tracks.added_by',
                                            $user->spotifyAccounts->pluck('id'))
                                )
                                ->sum('duration') / CarbonInterval::getMillisecondsPerSecond()))
                                ->totalSeconds / CarbonInterval::fromString(Arr::get((new Jam)(),
                                    'duration'))->totalSeconds) * 100,
                            2
                        ),
                        'total' => $userDuration->forHumans(),
                    ],
                ],
                // TODO Exclude compilation lists
                'participation' => round((Playlist::query()
                    ->whereHas('tracks', fn (Builder $trackBuilder) => $trackBuilder
                        ->whereIn('spotify_playlist_tracks.added_by',
                            $user->spotifyAccounts->pluck('id'))
                    )->count() / Playlist::query()->count()) * 100, 2),
            ],
            ]);
    }
}
