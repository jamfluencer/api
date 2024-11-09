<?php

namespace Database\Factories;

use App\Playback\Album;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Album>
 */
class AlbumFactory extends Factory
{
    protected $model = Album::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::random(),
            'name' => fake()->word(),
            'uri' => fake()->url(),
            'link' => fake()->url(),
        ];
    }
}
