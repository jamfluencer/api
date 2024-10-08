<?php

namespace App\Spotify;

use DateInterval;
use DateTime;
use Illuminate\Support\Collection;

readonly class AccessToken
{
    public Collection $scopes;

    public string $type;

    public DateTime $expiresAt;

    public function __construct(
        public string $token,
        public string $refresh,
        DateTime|int $expiry,
        string $scopes = ''
    ) {
        $this->type = 'Bearer';
        $this->scopes = collect(explode(' ', $scopes));
        $this->expiresAt = $expiry instanceof DateTime
            ? $expiry
            : (new DateTime)->add(new DateInterval("PT{$expiry}S"));
    }

    public function expired(): bool
    {
        return $this->expiresAt < new DateTime;
    }
}
