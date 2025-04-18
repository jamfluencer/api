<?php

namespace App\Slack;

enum Tab: string
{
    case HOME = 'home';
    case ABOUT = 'about';
    case MESSAGES = 'messages';
}
