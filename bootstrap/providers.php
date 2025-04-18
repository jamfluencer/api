<?php

use App\Slack\Provider as SlackProvider;
use App\Spotify\Provider as SpotifyProvider;

return [
    App\Providers\AppServiceProvider::class,
    SpotifyProvider::class,
    SlackProvider::class,
];
