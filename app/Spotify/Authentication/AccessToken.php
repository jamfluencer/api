<?php

namespace App\Spotify\Authentication;

use DateInterval;
use DateTime;
use Illuminate\Support\Collection;
use Stringable;

readonly class AccessToken implements Stringable
{
    public Collection $scopes;

    public string $type;

    public DateTime $expiresAt;

    public function __construct(
        public string $token,
        DateTime|int $expiry,
        public string $refresh,
        ?string $scopes = null
    ) {
        $this->type = 'Bearer';
        $this->scopes = collect(explode(' ', $scopes ?? ''));
        $this->expiresAt = $expiry instanceof DateTime
            ? $expiry
            : (new DateTime)->add(new DateInterval("PT{$expiry}S"));
    }

    public function expired(): bool
    {
        return $this->expiresAt < new DateTime;
    }

    public function __toString(): string
    {
        return $this->token;
    }
}
