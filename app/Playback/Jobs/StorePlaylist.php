<?php

namespace App\Playback\Jobs;

use App\Models\User;
use App\Playback\Artist as ArtistModel;
use App\Playback\Playlist;
use App\Playback\Track;
use App\Spotify\Artist;
use App\Spotify\Facades\Spotify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;

class StorePlaylist implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly User $user,
        private readonly string $id
    ) {}

    public function handle(): void
    {
        $playlist = Spotify::setToken($this->user->spotifyToken)->playlist($this->id, true);

        if ($playlist === null) {
            $this->fail('Failed to retrieve playlist.');
        }

        /** @var Playlist $playlistModel */
        $playlistModel = Playlist::query()->updateOrCreate(
            ['id' => $playlist->id],
            [
                'name' => $playlist->name,
                'url' => $playlist->url,
                'snapshot' => $playlist->snapshot,
            ]
        );

        if ($playlistModel->wasRecentlyCreated === false && $playlistModel->snapshot === $playlist->snapshot) {
            return; // Snapshot matches, no updates needed.
        }

        $playlistModel->tracks()->detach(); // Rebuild the playlist, syncing gets weird.

        foreach ($playlist->tracks as $track) {
            $playlistModel->tracks()->attach(
                $trackModel = tap(
                    Track::query()->updateOrCreate(['id' => $track->id], ['name' => $track->name]),
                    fn (Track $trackModel) => $trackModel->artists()
                        ->sync(Arr::pluck(array_map(
                            fn (Artist $artist) => ArtistModel::query()
                                ->firstOrCreate(['id' => $artist->id], ['name' => $artist->name, 'uri' => $artist->uri]),
                            $track->artists
                        ), 'id'))
                ),
                [
                    'added_by' => $playlist->collaborative === true
                        ? $track->added_by
                        : ($trackModel->first_occurrence?->pivot?->added_by ?? $track->added_by),
                ]
            );

        }
    }
}
