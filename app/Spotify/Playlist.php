<?php

namespace App\Spotify;

readonly class Playlist
{
    public function __construct(
        public string $name,
        public string $id,
        public array $images,
        public array $tracks,
        public int $totalTracks,
        public ?string $next
    ) {
    }

    public static function fromSpotify(array $spotify): self
    {
        return new self(
            name: $spotify['name'],
            id: $spotify['id'],
            images: array_map(fn (array $image) => new Image(...$image), $spotify['images']),
            tracks: array_map(fn (array $track) => Track::fromSpotify($track), $spotify['tracks']['items'] ?? []),
            totalTracks: $spotify['tracks']['total'],
            next: $spotify['tracks']['next']??null,
        );
    }

    public function extend(array $tracks, ?string $next = null): self
    {
        return new self(
            name: $this->name,
            id: $this->id,
            images: $this->images,
            tracks: $this->tracks + $tracks,
            totalTracks: $this->totalTracks,
            next: $next
        );
    }
}
