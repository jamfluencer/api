<?php

namespace App\Slack\Event;

use Illuminate\Http\JsonResponse;

class Handler
{
    public function __invoke(Details $event): JsonResponse
    {
        return response()->json();
    }
}
