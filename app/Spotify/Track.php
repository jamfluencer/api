<?php

namespace App\Spotify;

use Illuminate\Support\Collection;

readonly class Track
{
    public Collection $markets;

    public function __construct(
        public string $name,
        public ?Album $album,
        public array $artists,
        //        public string $href,
        public string $id,
        //        public string $uri,
        //        public bool $playable = true,
        //        Collection|array $markets = [],
        //        public ?int $disc = null,
        //        public ?int $duration = null,
        //        public bool|string $explicit = 'unknown',
        //        public ?string $irsc = null,
        //        public ?string $ean = null,
        //        public ?string $upc = null,
        //        public ?string $spotifyUrl = null,
        public ?string $added_by = null,
        ...$args
    ) {
        //        $this->markets = collect($markets);
    }

    public static function fromSpotify(array $item): self
    {
        if (isset($item['track'])) {
            $item['track']['added_by'] = $item['added_by'];
            $item = $item['track'];
        }

        return new self(
            album: new Album(...$item['album']),
            artists: array_map(fn (array $artist) => new Artist(...$artist), $item['artists']),
            name: $item['name'],
            //            href: $item['href'],
            id: $item['id'],
            //            uri: $item['uri'],
            added_by: $item['added_by']['id'] ?? null,
        );
    }
}
