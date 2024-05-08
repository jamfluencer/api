<?php

namespace App\Spotify;

class Playlist
{
    public function __construct(
        public readonly string $name,
        public readonly string $id,
        public readonly array $images,
        public readonly array $tracks,
    ) {
    }

    public static function fromSpotify(array $spotify): self
    {
        return new self(
            name: $spotify['name'],
            id: $spotify['id'],
            images: array_map(fn (array $image) => new Image(...$image), $spotify['images']),
            tracks: array_map(fn (array $track) => Track::fromSpotify($track), $spotify['tracks']['items'] ?? []),
        );
    }
}
