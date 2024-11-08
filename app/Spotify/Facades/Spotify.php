<?php

namespace App\Spotify\Facades;

use App\Spotify\Authentication\AccessToken;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Spotify\Spotify setToken(AccessToken $token)
 */
class Spotify extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'spotify';
    }
}
