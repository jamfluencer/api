<?php

namespace App\Playback\Jobs;

use App\Models\User;
use App\Playback\Playlist;
use App\Spotify\Facades\Spotify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class StorePlaylist implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly string $id
    ) {}

    public function handle()
    {
        try {
            $user = User::query()->findOrFail(Arr::get(Cache::get('jam', []), 'user'));
        } catch (ModelNotFoundException) {
            return; // No Jam is running.
        }
        $playlist = Spotify::setToken($user->spotifyToken)->playlist($this->id, true);

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
            $model->tracks()->create(['id' => $track->id], ['added_by' => $track->added_by]);
        }
    }
}
