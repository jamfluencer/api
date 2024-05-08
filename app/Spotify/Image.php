<?php

namespace App\Spotify;

readonly class Image
{
    public function __construct(
        public string $url,
        public ?int $width,
        public ?int $height,
    ) {
    }
}
