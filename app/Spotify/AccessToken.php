<?php

namespace App\Spotify;

use Illuminate\Support\Collection;

readonly class AccessToken
{
    public Collection $scopes;

    public string $type;

    public function __construct(
        public string $token,
        public string $refresh,
        public int $expiry,
        string $scopes = ''
    ) {
        $this->type = 'Bearer';
        $this->scopes = collect(explode(' ', $scopes));
    }
}
