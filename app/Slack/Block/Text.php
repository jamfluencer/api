<?php

namespace App\Slack\Block;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
class Text extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $type,
        public string $text,
        public Optional|bool $verbatim = false
    ) {}
}
