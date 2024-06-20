<?php

namespace App\Spotify;

/**
 * @property-read array<Track> $tracks
 */
readonly class Playlist
{
    public function __construct(
        public string $name,
        public string $id,
        public array $images,
        public array $tracks,
        public int $totalTracks,
        public ?string $next,
        public string $url,
        public string $snapshot
    ) {}

    public static function fromSpotify(array $spotify): self
    {
        return new self(
            name: $spotify['name'],
            id: $spotify['id'],
            images: array_map(fn (array $image) => new Image(...$image), $spotify['images']),
            tracks: array_map(fn (array $track) => Track::fromSpotify($track), $spotify['tracks']['items'] ?? []),
            totalTracks: $spotify['tracks']['total'],
            next: $spotify['tracks']['next'] ?? null,
            url: $spotify['external_urls']['spotify'],
            snapshot: $spotify['snapshot_id']
        );
    }

    public function extend(array $tracks, ?string $next = null): self
    {
        return new self(
            name: $this->name,
            id: $this->id,
            images: $this->images,
            tracks: array_merge($this->tracks, $tracks),
            totalTracks: $this->totalTracks,
            next: $next,
            url: $this->url,
            snapshot: $this->snapshot,
        );
    }
}
