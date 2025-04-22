<?php

namespace App\Slack\Block;

enum Type: string
{
    case HEADER = 'header';
    case SECTION = 'section';
    case DIVIDER = 'divider';
}
