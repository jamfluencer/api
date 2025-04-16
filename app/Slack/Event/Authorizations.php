<?php

namespace App\Slack\Event;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
class Authorizations extends Data
{
    public function __construct(
        public Optional|string $enterpriseId,
        public Optional|string $teamId,
        public Optional|string $userId,
    ) {}
}
