<?php

namespace App\Spotify;

readonly class Album
{
    public array $images;

    public function __construct(
        public string $id,
        public string $name,
        public string $uri,
        array $images = [],
        public array $external_urls = [],
        ...$args
    ) {
        $this->images = array_map(fn (Image|array $image) => is_array($image) === false && $image::class === Image::class
            ? $image
            : new Image(...$image), $images);
    }
}
