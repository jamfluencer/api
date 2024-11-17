<?php

use App\Models\User;
use App\Spotify\Authentication\AccessToken;
use App\Spotify\Facades\Spotify;
use App\Spotify\Profile;
use Illuminate\Support\Str;

it('converts code to token', function () {
    Spotify::shouldReceive('accessToken')->once()
        ->andReturn(new AccessToken(
            'token',
            3600,
            'refresh',
            's,c,o,p,e'
        ));
    Spotify::shouldReceive('setToken')->andReturnSelf();
    Spotify::shouldReceive('profile')->once()->andReturn(new Profile(
        'id',
        'display',
        [],
        [],
        [],
        ''
    ));

    $this->actingAs(User::factory()->create())
        ->POST('v1/spotify/auth', [
            'code' => Str::random(10),
        ])
        ->assertSuccessful();
});
