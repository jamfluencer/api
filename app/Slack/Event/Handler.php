<?php

namespace App\Slack\Event;

use Illuminate\Http\JsonResponse;

class Handler
{
    public function __invoke(): JsonResponse
    {
        return response()->json();
    }
}
