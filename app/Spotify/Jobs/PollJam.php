<?php

namespace App\Spotify\Jobs;

use App\Models\User;
use App\Playback\Playlist;
use App\Spotify\Events\JamEnded;
use App\Spotify\Events\JamUpdate;
use App\Spotify\Facades\Spotify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PollJam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if (Cache::has('jam') === false) {
            return;
        }

        $user = User::query()->findOrFail(Arr::get(Cache::get('jam', []), 'user'));

        $track = Spotify::setToken($user->spotifyToken->forSpotify())->currentlyPlaying();

        if ($track === null) {
            Cache::forget('jam');
            JamEnded::broadcast();

            return;
        }

        $playlist = Str::afterLast($track->context?->uri ?? '', ':');

        JamUpdate::dispatchIf(
            $track->id !== Arr::get(Cache::get('jam', []), 'currently_playing'),
            true,
            $playlist !== Arr::get(Cache::get('jam', []), 'playlist')
            || Spotify::setToken($user->spotifyToken->forSpotify())->playlist($playlist)?->snapshot !== Playlist::query()->find($playlist)?->snapshot
        );
        Cache::put('jam', array_merge(Cache::get('jam'), [
            'currently_playing' => $track->id,
            'playlist' => $playlist,
        ]));

        self::dispatch()->delay(now()->addSeconds(5));
    }
}
