<?php

namespace App\Slack;

use App\Slack\Services\Service;
use App\Slack\Services\Views\Service as Views;
use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Slack
{
    private Collection $services;

    public function __construct(
        private readonly string $botToken
    ) {
        $this->services = collect();
    }

    public function views(): Views
    {
        return app(Views::class);
    }

    /**
     * @param  Closure(): Service  $builder
     */
    private function service(string $name, Service $builder): Service
    {
        return $this->services->getOrPut($name, $builder);
    }

    public function client(): PendingRequest
    {
        return Http::withHeader('Authorization', "Bearer {$this->botToken}")
            ->baseUrl('https://slack.com/api/')
            ->acceptJson();
    }
}
