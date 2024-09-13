<?php

namespace Database\Factories;

use App\Playback\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => fake()->url(),
            'width' => fake()->numberBetween(1, 100),
            'height' => fake()->numberBetween(1, 100),
        ];
    }
}
