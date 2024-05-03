<?php

namespace App\Spotify;

class Queue
{
    public function __construct(
        public readonly Track $currently_playing,
        public readonly array $queue
    )
    {}
    public static function fromSpotify(array $spotify): self
    {
        return new self(
            currently_playing: Track::fromSpotify($spotify['currently_playing']),
            queue: array_map(
                fn (array $track) => Track::fromSpotify($track),
                $spotify['queue']
            )
        );
    }
}
