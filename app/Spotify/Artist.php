<?php

namespace App\Spotify;

readonly class Artist
{
    public function __construct(
        public string $id,
        public string $name,
        public string $uri,
        public array $external_urls,
        ...$args
    ) {}
}
