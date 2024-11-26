<?php

namespace App\Social\Statistics;

use App\Models\User as UserModel;
use App\Playback\Track;
use App\Social\Kudos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class User
{
    public function __invoke(UserModel $user): array
    {
        return [
            'social' => [
                'kudos' => Kudos::query()
                    ->whereHas('forSpotifyAccount', fn (Builder $spotifyAccountBuilder) => $spotifyAccountBuilder
                        ->whereHas('user', fn (Builder $userBuilder) => $userBuilder->where('id', $user->id)))
                    ->count(),
                'tracks' => [
                    'most-appreciated' => [
                        'details' => ($mostAppreciated = Track::query()
                            ->withCount('kudos')
                            ->with([
                                'playlists' => fn (Relation $playlistBuilder) => $playlistBuilder
                                    ->whereIn('spotify_playlist_tracks.added_by', $user->spotifyAccounts->pluck('id')),
                            ])
                            ->with([
                                'albums',
                                'artists',
                            ])
                            ->orderBy('kudos_count', 'desc')->first())->toArray(),
                        'kudos' => $mostAppreciated->kudos_count,
                    ],
                    'mean' => round(Track::query()
                        ->with([
                            'playlists' => fn (Relation $playlistBuilder) => $playlistBuilder
                                ->whereIn('spotify_playlist_tracks.added_by', $user->spotifyAccounts->pluck('id')),
                        ])
                        ->withCount('kudos')
                        ->get('kudos_count')
                        ->avg('kudos_count'), 2),
                ],
            ],
            'jam' => [
                'tracks' => [
                    'count' => [
                        'percentage' => null,
                        'total' => null,
                    ],
                    'duration' => [
                        'percentage' => null,
                        'total' => null,
                    ],
                ],
                'participation' => null,
            ],
        ];
    }
}
