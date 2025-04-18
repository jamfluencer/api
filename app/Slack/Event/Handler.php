<?php

namespace App\Slack\Event;

use App\Slack\Block\Type as BlockType;
use App\Slack\Services\Views\Responses\Publish;
use App\Slack\View\Data as View;
use App\Slack\View\Type as ViewType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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
        /** @var Publish $response */
        $response = app('slack')->views()->publish(
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

        if ($response->ok === false) {
            Log::alert("Home tab failed to publish: {$response->error}");
        }
    }
}
