<?php

namespace App\Playback\Concerns;

use App\Playback\Image;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasImages
{
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'related');
    }
}
