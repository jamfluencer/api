<?php

namespace Database\Factories;

use App\Playback\Playlist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends Factory<Playlist>
 */
class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->name(),
            'url' => $this->faker->url(),
            'snapshot' => Str::random(),
        ];
    }
}
