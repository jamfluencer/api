<?php

namespace App\Spotify\Events;

use App\Spotify\Playlist;
use App\Spotify\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class JamStarted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        private readonly Playlist $playlist,
        private readonly Queue $queue
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('jam'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'jam.start';
    }

    public function broadcastWith(): array
    {
        return [
            'queue' => (array) $this->queue,
            'playlist' => (array) $this->playlist,
        ];
    }
}
