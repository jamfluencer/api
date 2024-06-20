<?php

namespace Database\Factories;

use App\Models\User;
use App\Playback\SpotifyAccount;
use App\Playback\SpotifyToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SpotifyAccount>
 */
class SpotifyAccountFactory extends Factory
{
    protected $model = SpotifyAccount::class;

    public function definition(): array
    {
        return [
            'id' => Str::random(),
            'country' => $this->faker->countryCode(),
            'display_name' => $this->faker->name(),
            'user_id' => User::factory(),
        ];
    }

    public function authorized(): static
    {
        return $this->afterCreating(
            fn (SpotifyAccount $account) => $account->token()->save(
                SpotifyToken::factory()->create()
            )
        );
    }
}
