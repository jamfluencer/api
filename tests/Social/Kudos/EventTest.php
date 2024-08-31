<?php

use App\Playback\Playlist;
use App\Playback\SpotifyAccount;
use App\Playback\Track;
use App\Social\Events\Kudos;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

it('dispatches the kudos event', function () {
    Event::fake();

    Cache::put(
        'jam',
        [
            'playlist' => $playlist = Playlist::factory()
                ->hasAttached(
                    $track = Track::factory()->create(),
                    ['added_by' => $account = SpotifyAccount::factory()->create()->id]
                )
                ->create()->id,
            'user' => $account,
            'currently_playing' => $track->id,
        ]
    );

    $this->postJson('/v1/jam/kudos')
        ->assertAccepted();

    Event::assertDispatched(Kudos::class);
});
