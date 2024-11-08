<?php

namespace App\Spotify\Jobs;

use App\Models\User;
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

class PollJam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if (Cache::has('jam') === false) {
            return;
        }

        $user = User::query()->findOrFail(Arr::get(Cache::get('jam', []), 'user'));

        $queue = Spotify::setToken($user->spotifyToken->forSpotify())->queue();

        if ($queue === null) {
            Cache::forget('jam');
            JamEnded::broadcast();

            return;
        }

        if ($queue->currently_playing?->id !== Arr::get(Cache::get('jam', []), 'currently_playing')) {
            JamUpdate::dispatch(); // TODO Include if Playlist snapshot changed.
            Cache::put('jam', array_merge(Cache::get('jam'), ['currently_playing' => $queue->currently_playing->id]));
        }

        self::dispatch()->delay(now()->addSeconds(3));
    }
}
