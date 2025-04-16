<?php

namespace App\Slack;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VerifySlackSignature
{
    public function handle(Request $request, Closure $next): mixed
    {
        $signature = $request->header('x-slack-signature', $request->header('X-Slack-Signature'));
        if (! $signature) {
            Log::alert('No valid signature header');

            return $this->reject();
        }
        $timestamp = $request->header('X-Slack-Request-Timestamp', $request->header('x-slack-request-timestamp'));
        $digest = hash_hmac(
            'sha256',
            $base = implode(
                ':',
                [
                    $version = Str::before($signature, '='),
                    $timestamp,
                    $request->getContent(),
                ]
            ),
            Config::get('slack.signing_key')
        );

        if (hash_equals($signature, "{$version}={$digest}") === false) {
            Log::alert("Invalid signature - {$signature} !== {$version}={$digest}");

            return $this->reject();
        }

        return $next($request);
    }

    private function reject(): JsonResponse
    {
        return response()->json(['message' => 'Invalid signature'], Response::HTTP_UNAUTHORIZED);
    }
}
