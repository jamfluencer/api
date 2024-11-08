<?php

namespace App\Spotify\Authentication;

use DateInterval;
use DateTime;
use Stringable;

readonly class ClientToken implements Stringable
{
    public DateTime $expiresAt;

    public function __construct(
        public string $value,
        DateTime|int $expiry
    ) {
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
        return $this->value;
    }
}
