<?php

namespace Database\Factories;

use App\Playback\SpotifyAccount;
use App\Playback\SpotifyToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SpotifyToken>
 */
class SpotifyTokenFactory extends Factory
{
    protected $model = SpotifyToken::class;

    public function definition(): array
    {
        return [
            'token' => Str::random(),
            'refresh' => Str::random(),
            'expires_at' => now()->addMinutes(10),
            'spotify_account_id' => SpotifyAccount::factory(),
        ];
    }
}
