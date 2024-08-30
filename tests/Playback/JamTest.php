<?php

use App\Models\User;
use App\Playback\Jobs\StorePlaylist;
use App\Spotify\Events\JamStarted;
use App\Spotify\Jobs\PollJam;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

describe('V1', function () {
    it('starts the jam', function () {})->todo();
});
describe('V2', function () {
    it('starts the jam', function () {
        Event::fake();
        Queue::fake();
        Http::fake([
            '*' => Http::response(),
        ]);
        $this
            ->actingAs(User::factory()->withSpotify()->create())
            ->postJson(
                '/v2/jam/start',
                [
                    'playlist' => 'spotify:playlist:abcdef',
                    'jam' => 'https://spotify.link/wxyz',
                ]
            )
            ->assertOk();

        Event::assertDispatched(JamStarted::class);
        Queue::assertPushed(PollJam::class);
        Queue::assertPushed(StorePlaylist::class);
    });
});
