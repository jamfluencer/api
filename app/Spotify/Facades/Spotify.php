<?php

namespace App\Spotify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Spotify\Spotify
 */
class Spotify extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'spotify';
    }
}
