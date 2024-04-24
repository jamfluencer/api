<?php

namespace App\Spotify;

use Illuminate\Support\Collection;

readonly class Track
{
    public Collection $markets;

    public function __construct(
        public string $name,
        public Album $album,
        public Collection $artists,
        public string $href,
        public string $id,
        public string $uri,
        public bool $playable = false,
        Collection|array $markets = [],
        public ?int $disc = null,
        public ?int $duration = null,
        public bool|string $explicit = 'unknown',
        public ?string $irsc = null,
        public ?string $ean = null,
        public ?string $upc = null,
        public ?string $spotifyUrl = null,
    ) {
        $this->markets = collect($markets);
    }

    public static function fromSpotify(array $item): self
    {
        return new self(
            album: Album::fromSpotify($item['album']),
            artists: collect(),
            name: $item['name'],
            href: $item['href'],
            id: $item['id'],
            uri: $item['uri']
        );
    }
}
