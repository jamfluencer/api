<?php

namespace App\Slack;

use Illuminate\Http\Response;

class Events
{
    public function __invoke(): Response
    {
        return response();
    }
}
