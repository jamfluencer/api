<?php

namespace App\Spotify\Authentication;

use Stringable;

readonly class RefreshToken implements Stringable
{
    public function __construct(public string $value) {}

    public function __toString(): string
    {
        return $this->value;
    }
}
