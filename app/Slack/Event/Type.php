<?php

namespace App\Slack\Event;

enum Type: string
{
    case URL_VERIFICATION = 'url_verification';
    case EVENT_CALLBACK = 'event_callback';
    case APP_DELETED = 'app_deleted';
    case APP_HOME_OPENED = 'app_home_opened';
    // TODO This is very much incomplete.
}
