<?php

namespace App\Spotify\Jobs;

use App\Playback\SpotifyAccount;
use App\Spotify\Facades\Spotify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $id) {}

    public function __invoke(): void
    {
        $profile = Spotify::withClientCredentials()->profile($this->id);

        SpotifyAccount::query()->updateOrCreate(
            ['id' => $profile->id],
            [
                'country' => $profile->country,
                'display_name' => $profile->display_name,
            ]
        );
    }
}
