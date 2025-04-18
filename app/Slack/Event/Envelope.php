<?php

namespace App\Slack\Event;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
class Envelope extends Data
{
    public function __construct(
        #[WithCast(EnumCast::class)]
        public Type $type,
        public string $token,
        public Optional|string $teamId,
        #[MapInputName('api_app_id')]
        public Optional|string $appId,
        public Optional|Authorizations $authorizations,
        #[MapInputName('event_context')]
        public Optional|string $context,
        #[MapInputName('event_id')]
        public Optional|string $id,
        #[MapInputName('event_time')]
        public Optional|int $time,
        #[WithCast(EventCast::class)]
        public Optional|Details $event,
        public Optional|string|null $challenge = null
    ) {}
}
