<?php

namespace App\Slack\Event;

use App\Slack\Block\Type as BlockType;
use App\Slack\View\Data as View;
use App\Slack\View\Type as ViewType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class Handler
{
    /**
     * @template T of Details
     *
     * @param  Details<T>  $event
     */
    public function __invoke(Details $event): JsonResponse
    {
        if (method_exists($this, $method = Str::camel($event->type))) {
            $this->{$method}($event);
        }

        event($event);

        return response()->json();
    }

    private function appHomeOpened(AppHomeOpened $event): void
    {
        app('slack')->views()->publish(
            'UTZ2VAYNB',
            View::from([
                'type' => ViewType::HOME->value,
                'blocks' => [
                    [
                        'type' => BlockType::HEADER->value,
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'We :heart: the Jam',
                            'emoji' => true,
                        ],
                    ],
                ],
            ])
        );
    }
}
