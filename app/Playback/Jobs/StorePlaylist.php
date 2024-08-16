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

    public function handle()
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

        foreach ($playlist->tracks as $track) {
            $model->tracks()->save(
                Track::query()->firstOrCreate(['id' => $track->id]),
                ['added_by' => $track->added_by]
            );
        }
    }
}
