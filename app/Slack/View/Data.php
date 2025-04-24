<?php

namespace App\Slack\View;

use App\Slack\Block\Data as Block;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data as LaravelData;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
class Data extends LaravelData
{
    public function __construct(
        public Optional|string $id,
        public Optional|string $teamId,
        #[WithCast(EnumCast::class)]
        public Type $type,
        public Optional|string|null $close = null,
        public Optional|string|null $submit = null,
        #[DataCollectionOf(Block::class)]
        public DataCollection $blocks,
        public Optional|string|null $callbackId = null,
        public Optional|State|null $state = null,
        public Optional|string|null $hash = null,
        public Optional|bool|null $clearOnClose = null,
        public Optional|bool|null $notifyOnClose = null,
        public Optional|string|null $rootViewId = null,
        public Optional|string|null $appId = null,
        public Optional|string|null $externalId = null,
        public Optional|string|null $botId = null,
        public Optional|string|null $privateMetadata = null,
    ) {}
}
