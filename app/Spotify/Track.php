<?php

namespace App\Spotify;

use Illuminate\Support\Collection;

readonly class Track extends Item
{
    public Collection $markets;

    public ?Context $context;

    public function __construct(
        public string $name,
        public ?Album $album,
        public array $artists,
        //        public string $href,
        public string $id,
        public ?string $url = null,
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
        ?array $context = null,
        ...$args
    ) {
        //        $this->markets = collect($markets);
        if ($context) {
            $this->context = new Context(...$context);
        }
    }

    public static function fromSpotify(array $item): self
    {
        if (isset($item['track'])) {
            $item['track']['added_by'] = $item['added_by'];
            $item = $item['track'];
        }

        return new self(
            name: $item['name'],
            album: new Album(...$item['album']),
            artists: array_map(fn (array $artist) => new Artist(...$artist), $item['artists']),
            //            href: $item['href'],
            id: $item['id'],
            url: $item['external_urls']['spotify'] ?? null,
            added_by: $item['added_by']['id'] ?? null,
        );
    }
}
