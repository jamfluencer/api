<?php

namespace App\Social\Events;

use App\Social\Kudos as KudosModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Kudos implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        /** @noinspection PhpPropertyCanBeReadonlyInspection */
        private KudosModel|ModelIdentifier $kudos
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('jam')];
    }

    public function broadcastAs(): string
    {
        return 'kudos';
    }

    public function broadcastWith(): array
    {
        return $this->kudos->toArray();
    }
}
