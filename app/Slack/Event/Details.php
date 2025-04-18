<?php

namespace App\Slack\Event;

use Spatie\LaravelData\Data;

abstract class Details extends Data
{
    public function __construct(public string $type) {}
}
