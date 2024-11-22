<?php

namespace App\Spotify;

readonly class CurrentlyPlayingTrack
{
    public function __construct(
        public Item $item,
        public Context $context,
    ) {}

    public static function fromSpotify(array $spotify): self
    {
        return new self(
            item: Track::fromSpotify($spotify['item']),
            context: new Context(...$spotify['context']),
        );
    }
}
