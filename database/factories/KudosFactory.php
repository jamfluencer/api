<?php

namespace Database\Factories;

use App\Models\User;
use App\Playback\Playlist;
use App\Playback\Track;
use App\Social\Kudos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Social\Kudos>
 */
class KudosFactory extends Factory
{
    protected $model = Kudos::class;

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
                ->hasAttached(Playlist::find($attributes['playlist_id']), ['added_by' => User::factory()->withSpotify()->create()->spotifyAccounts->first()->id])
                ->create(),
            'for_spotify_account_id' => fn (array $attributes) => Playlist::find($attributes['playlist_id'])->tracks()->find($attributes['track_id'])->pivot->added_by,
        ];
    }
}
