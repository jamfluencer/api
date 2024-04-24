<?php

namespace App\Spotify;

class Album
{
    public static function fromSpotify(array $item): self
    {
        return new self();
    }
}
