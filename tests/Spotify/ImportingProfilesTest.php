<?php

use App\Playback\SpotifyAccount;
use App\Spotify\Facades\Spotify;
use App\Spotify\Jobs\ImportUser;
use App\Spotify\Profile;
use Illuminate\Support\Str;

it('supports unrelated profiles', function () {
    Spotify::shouldReceive('withClientCredentials')->once()->andReturnSelf();
    Spotify::shouldReceive('profile')->once()->andReturn(new Profile(
        id: $id = Str::random(),
        display_name: 'Fake Account',
        external_urls: [],
        followers: [],
        images: [],
        uri: 'spotify:profile:merlin'
    ));

    ImportUser::dispatchSync($id);

    expect(SpotifyAccount::query()->where('id', $id)->exists())->toBeTrue();
});

it('updates existing profiles', function () {
    $account = SpotifyAccount::factory()->create();
    Spotify::shouldReceive('withClientCredentials')->once()->andReturnSelf();
    Spotify::shouldReceive('profile')->once()->andReturn(new Profile(
        id: $account->id,
        display_name: 'Fake Account',
        external_urls: [],
        followers: [],
        images: [],
        uri: 'spotify:profile:merlin'
    ));

    ImportUser::dispatchSync($account->id);
    $account->refresh();

    expect($account->id)->toBe($account->id)
        ->and($account->display_name)->toBe('Fake Account');
});
