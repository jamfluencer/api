<?php

namespace App\Slack\Event;

use Illuminate\Support\Str;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class EventCast implements Cast
{
    /**
     * @param  array  $value
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): Details|Uncastable
    {
        /** @var $class class-string<Details> */
        if (class_exists($class = __NAMESPACE__.'\\'.Str::studly($value['type'])) === false) {
            return Uncastable::create();
        }

        return $class::from($value);
    }
}
