<?php

namespace Database\Factories;

use App\Playback\Track;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    protected $model = Track::class;

    public function definition(): array
    {
        return [
            'id' => Str::random(),
            'name' => fake()->words(3, true),
            'url' => fake()->url(),
            'duration' => fake()->numberBetween(1, 10) * CarbonInterval::getMillisecondsPerSecond(),
        ];
    }
}
