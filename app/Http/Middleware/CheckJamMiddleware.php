<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckJamMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Cache::has('jam')) {
            return response()->json(['message' => 'NO JAM FOR YOU'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
