<?php

namespace App\Listeners;

use App\Spotify\Events\JamStatus;
use Laravel\Reverb\Events\MessageReceived;

class WebsocketMessages
{
    public function __invoke(MessageReceived $event): void
    {
        $message = json_decode($event->message);

        match ($message?->event) {
            'pusher:subscribe' => JamStatus::broadcast(),
            default => 1
        };
    }
}
