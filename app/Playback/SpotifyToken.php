<?php

namespace App\Playback;

use Illuminate\Database\Eloquent\Model;

class SpotifyToken extends Model
{
    public $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
}
