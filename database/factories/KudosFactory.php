<?php

namespace Database\Factories;

use App\Models\User;
use App\Playback\Playlist;
use App\Playback\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kudos>
 */
class KudosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'playlist_id' => Playlist::factory(),
            'track_id' => fn (array $attributes) => Track::factory()
                ->hasAttached(Playlist::find($attributes['playlist_id']), ['added_by' => User::factory()->create()->id])
                ->create(),
            'for_user_id' => fn (array $attributes) => Playlist::find($attributes['playlist_id'])->tracks()->find($attributes['track_id'])->pivot->added_by,
            'from_user_id' => User::factory(),
        ];
    }
}
