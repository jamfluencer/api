<?php

namespace App\Slack;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

abstract class Response extends Data
{
    public function __construct(
        public bool $ok,
        public Optional|string $error,
        public Optional|string $warning
    ) {}
}
