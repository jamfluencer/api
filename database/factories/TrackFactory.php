<?php

namespace Database\Factories;

use App\Playback\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    public function definition(): array
    {
        return ['id' => Str::random()];
    }
}
