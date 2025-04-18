<?php

namespace App\Spotify;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->alias(Spotify::class, 'spotify');
        $this->app->bind(Spotify::class, fn () => new Spotify(
            id: config('spotify.id'),
            secret: config('spotify.secret'),
        ));
    }

    public function provides(): array
    {
        return [
            'spotify',
            Spotify::class,
        ];
    }
}
