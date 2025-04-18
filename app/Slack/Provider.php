<?php

namespace App\Slack;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->alias(Slack::class, 'slack');
        $this->app->singleton(Slack::class, fn () => new Slack(
            botToken: config('slack.bot.token')
        ));
    }

    public function provides(): array
    {
        return [
            'slack',
            Slack::class,
        ];
    }
}
