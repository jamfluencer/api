<?php

namespace App\Slack\Event;

use App\Slack\Tab;
use App\Slack\View\Data;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
class AppHomeOpened extends Details
{
    public function __construct(
        public string $type,
        public string $user,
        public string $channel,
        public int $eventTs,
        #[WithCast(EnumCast::class)]
        public Tab $tab,
        public Optional|Data $view
    ) {
        parent::__construct($type);
    }
}
