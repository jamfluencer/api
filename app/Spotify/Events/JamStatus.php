<?php

namespace App\Spotify\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Cache;

class JamStatus implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;

    public function broadcastAs(): string
    {
        return 'jam.status';
    }

    public function broadcastOn(): array
    {
        return [new Channel('jam')];
    }

    public function broadcastWith(): array
    {
        return ['active' => Cache::has('jam')];
    }
}
