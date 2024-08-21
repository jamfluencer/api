<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::macro(
            'fromComponents',
            fn (array $components) => ($components['scheme'] ?? false
                    ? Str::finish(
                        $components['scheme'],
                        '://'
                    )
                    : '//')
                .($components['host'] ?? '')
                .($components['port'] ?? false ? Str::start($components['port'] ?? '', ':') : '')
                .($components['path'] ?? '')
                .($components['query'] ?? false ? Str::start($components['query'], '?') : '')
                .($components['fragment'] ?? false ? Str::start($components['fragment'] ?? '', '#') : '')
        );

        RateLimiter::for(
            'kudos',
            fn (Request $request) => Limit::perMinutes(2, 1)
                ->by($request->user()?->id ?: $request->ip())
        );
    }
}
