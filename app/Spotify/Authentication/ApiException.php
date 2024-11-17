<?php

namespace App\Spotify\Authentication;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ApiException extends Exception
{
    public function __construct(?array $error = [])
    {
        parent::__construct($this->message ??
            Str::rtrim(Str::finish(Arr::get($error, 'error'), ': '), ': ')
            .Arr::get($error, 'error_description', 'Unknown API error')
        );
    }
}
