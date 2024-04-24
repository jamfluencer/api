<?php

namespace App\Providers;

use App\Spotify\Spotify;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SpotifyServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->bind('spotify', fn () => new Spotify(
            id: config('spotify.id'),
            secret: config('spotify.secret'),
        ));
    }

    public function provides(): array
    {
        return ['spotify'];
    }
}
