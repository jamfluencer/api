<?php

namespace App\Slack\Block;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data as LaravelData;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
class Data extends LaravelData
{
    public function __construct(
        public Type $type,
        public Optional|string $blockId,
        public Optional|Text $text,
    ) {}
}
