<?php

namespace App\Slack\Services\Views\Responses;

use App\Slack\Response;
use App\Slack\View\Data;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
class Publish extends Response
{
    public function __construct(
        public bool $ok,
        public Optional|string $error,
        public Optional|string $warning,
        public Optional|Data $view,
        public Optional|string $responseMetadata
    ) {
        parent::__construct($ok, $error, $warning);
    }
}
