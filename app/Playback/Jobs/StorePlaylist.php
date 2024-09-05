<?php

namespace App\Playback\Jobs;

use App\Models\User;
use App\Playback\Playlist;
use App\Playback\Track;
use App\Spotify\Facades\Spotify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

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

        /** @var Playlist $model */
        $model = Playlist::query()->createOrFirst(
            ['id' => $playlist->id],
            [
                'name' => $playlist->name,
                'url' => $playlist->url,
                'snapshot' => $playlist->snapshot,
            ]
        );

        if ($model->wasRecentlyCreated === false && $model->snapshot === $playlist->snapshot) {
            return; // Snapshot matches, no updates needed.
        }

        $model->tracks()->detach(); // Rebuild the playlist, syncing gets weird.

        foreach ($playlist->tracks as $track) {
            $model->tracks()->attach(
                Track::query()->firstOrCreate(['id' => $track->id], ['name' => $track->name]),
                ['added_by' => $track->added_by]
            );
        }
    }
}
