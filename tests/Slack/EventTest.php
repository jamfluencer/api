<?php

use App\Slack\Event\AppHomeOpened;
use App\Slack\Services\Views\Responses\Publish;
use App\Slack\VerifySlackSignature;
use Illuminate\Support\Facades\Event;
use Tests\Slack\SlackStub;

uses(SlackStub::class);

it('handles home opened event', function () {
    Event::fake();

    $this->slack->shouldReceive('publish')->once()->andReturn(Publish::from([
        'ok' => true,
        'view' => [
            'id' => '',
            'team_id' => '',
            'type' => 'home',
            'close' => null,
            'submit' => null,
            'blocks' => [],
            'private_metadata' => '',
            'callback_id' => '',
            'state' => [],
            'hash' => '',
            'clear_on_close' => false,
            'notify_on_close' => false,
            'root_view_id' => '',
            'previous_view_id' => '',
            'app_id' => '1',
            'external_id' => '',
            'bot_id' => '',
        ],
    ]));

    $this->withoutExceptionHandling()->withoutMiddleware([VerifySlackSignature::class])->postJson(
        'slack/events',
        [
            'type' => 'event_callback',
            'token' => 'XXYYZZ',
            'team_id' => 'T123ABC456',
            'api_app_id' => 'A123ABC456',
            'event' => [
                'type' => 'app_home_opened',
                'user' => 'U123ABC456',
                'channel' => 'D123ABC456',
                'event_ts' => '1515449522000016',
                'tab' => 'home',
                'view' => [
                    'id' => 'V123ABC456',
                    'team_id' => 'T123ABC456',
                    'type' => 'home',
                    'blocks' => [],
                    'private_metadata' => 'shh',
                    'callback_id' => '456',
                    'hash' => '1231232323.12321312',
                    'clear_on_close' => false,
                    'notify_on_close' => false,
                    'root_view_id' => 'V123ABC456',
                    'app_id' => 'A123ABC456',
                    'external_id' => '4545',
                    'app_installed_team_id' => 'T123ABC456',
                    'bot_id' => 'B123ABC456',
                ],
            ],
            'event_context' => 'EC123ABC456',
            'event_id' => 'Ev123ABC456',
            'event_time' => 1234567890,
            'authorizations' => [
                [
                    'enterprise_id' => 'E123ABC456',
                    'team_id' => 'T123ABC456',
                    'user_id' => 'U123ABC456',
                    'is_bot' => false,
                    'is_enterprise_install' => false,
                ],
            ],
            'is_ext_shared_channel' => false,
            'context_team_id' => 'T123ABC456',
            'context_enterprise_id' => null,
        ]
    )->assertOk();

    Event::assertDispatched(AppHomeOpened::class);
});
