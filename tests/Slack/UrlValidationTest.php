<?php

use App\Slack\VerifySlackSignature;
use Illuminate\Testing\Fluent\AssertableJson;

it('returns challenge', function () {
    test()->withoutMiddleware(VerifySlackSignature::class)->postJson(
        '/slack/events',
        $payload = [
            'token' => 'Jhj5dZrVaK7ZwHHjRyZWjbDl',
            'challenge' => '3eZbrw1aBm2rZgRNFdxV2595E9CY3gmdALWMmHkvFXO7tYXAYM8P',
            'type' => 'url_verification',
        ]
    )->assertJson(fn (AssertableJson $response) => $response
        ->where('challenge', $payload['challenge']));
});
