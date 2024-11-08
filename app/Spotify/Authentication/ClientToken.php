<?php

namespace App\Spotify\Authentication;

use DateTime;
use Stringable;

readonly class ClientToken implements Stringable
{
    public function __construct(
        public string $value,
        public DateTime $expiresAt
    ) {}

    public function expired(): bool
    {
        return $this->expiresAt < new DateTime;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
