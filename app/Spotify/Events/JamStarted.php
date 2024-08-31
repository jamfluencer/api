<?php

namespace App\Spotify\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class JamStarted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        public ?string $url
    ) {}

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
}
