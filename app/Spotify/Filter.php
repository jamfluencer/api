<?php

namespace App\Spotify;

readonly class Filter
{
    public function __construct(
        public bool $enabled,
        public bool $locked
    ){}
}
