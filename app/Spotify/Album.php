<?php

namespace App\Spotify;

readonly class Album
{
    public array $images;
    public function __construct(
        public string $id,
        public string $name,
        array $images,
        ...$args
    ){
        $this->images = array_map(fn (array $image) => new Image(...$image),$images);
    }
}
