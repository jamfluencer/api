<?php

namespace App\Spotify;

enum ContextType: string
{
    case ARTIST = 'artist';
    case PLAYLIST = 'playlist';
    case ALBUM = 'album';
    case SHOW = 'show';
}
