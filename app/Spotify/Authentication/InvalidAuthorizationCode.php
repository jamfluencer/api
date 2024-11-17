<?php

namespace App\Spotify\Authentication;

class InvalidAuthorizationCode extends ApiException
{
    protected $message = 'Invalid authorization code';
}
