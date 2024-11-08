<?php

namespace App\Spotify\Facades;

use App\Spotify\Authentication\AccessToken;
use App\Spotify\Authentication\RefreshToken;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Spotify\Spotify setToken(AccessToken $token)
 * @method static AccessToken refreshToken(RefreshToken $token)
 * @method static \App\Spotify\Spotify withClientCredentials()
 */
class Spotify extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'spotify';
    }
}
