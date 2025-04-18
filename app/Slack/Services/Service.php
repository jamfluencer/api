<?php

namespace App\Slack\Services;

use App\Slack\Slack;

interface Service
{
    public function __construct(Slack $client);
}
