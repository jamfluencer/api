<?php

namespace App\Slack;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class BoolTransformer implements Transformer
{
    /**
     * @param  bool  $value
     */
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): string
    {
        return $value ? 'true' : 'false';
    }
}
