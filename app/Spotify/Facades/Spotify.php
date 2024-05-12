<?php

namespace App\Spotify\Facades;

use App\Playback\SpotifyToken;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Spotify\Spotify setToken(SpotifyToken $token)
 */
class Spotify extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'spotify';
    }
}
