<?php

namespace App\Slack\Services\Views;

use App\Slack\Response;
use App\Slack\Services\Service as ServiceInterface;
use App\Slack\Services\Views\Responses\Publish;
use App\Slack\Slack;
use App\Slack\View\Data;
use Illuminate\Support\Facades\Log;

class Service implements ServiceInterface
{
    public function __construct(private readonly Slack $slack) {}

    public function update(string $id, Data $view): Response
    {
        return Response::from($this->slack->client()->post(
            'views.update',
            [
                'view_id' => $id,
                'view' => $view,
            ]
        ));
    }

    public function publish(string $id, Data $view): Publish
    {
        return Publish::from(tap($this->slack->client()->post(
            'views.publish',
            [
                'user_id' => $id,
                'view' => $view->except('blocks.text.verbatim'),
            ]
        ), fn (\Illuminate\Http\Client\Response $response) => Log::debug($response->json()))->json());
    }
}
