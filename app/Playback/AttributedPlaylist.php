<?php

namespace App\Playback;

use App\Spotify\Playlist;

readonly class AttributedPlaylist
{
    private function __construct(
        public string $name,
        public string $id,
        public array $images,
        public array $tracks,
        public int $totalTracks,
        public string $url,
    ) {}

    public function __get(string $name): mixed
    {
        return $this->playlist->{$name};
    }

    public static function attribution(Playlist $playlist): self
    {
        return new self(
            name: $playlist->name,
            id: $playlist->id,
            images: $playlist->images,
            tracks: array_map(
                fn (\App\Spotify\Track $track) => new \App\Spotify\Track(
                    name: $track->name,
                    album: $track->album,
                    artists: $track->artists,
                    id: $track->id,
                    url: $track->url,
                    added_by: Track::query()->find($track->id)->firstOccurrence->pivot->added_by,
                ),
                $playlist->tracks
            ),
            totalTracks: $playlist->totalTracks,
            url: $playlist->url,
        );
    }
}
