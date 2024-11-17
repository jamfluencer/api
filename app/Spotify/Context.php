<?php

namespace App\Spotify;

readonly class Context
{
    public ?ContextType $type;

    public function __construct(
        string $type,
        public string $href,
        public array $external_urls,
        public string $uri
    ) {
        $this->type = ContextType::tryFrom($type);
    }
}
