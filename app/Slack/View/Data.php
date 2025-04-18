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
        public Optional|string|null $close,
        public Optional|string|null $submit,
        #[DataCollectionOf(Block::class)]
        public DataCollection $blocks,
        public Optional|string $privateMetadata,
        public Optional|string $callbackId,
        public Optional|State $state,
        public Optional|string $hash,
        public Optional|bool $clearOnClose,
        public Optional|bool $notifyOnClose,
        public Optional|string $rootViewId,
        public Optional|string $appId,
        public Optional|string $externalId,
        public Optional|string $botId
    ) {}
}
