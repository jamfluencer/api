<?php

namespace App\Playback;

use App\Spotify\AccessToken;
use Illuminate\Database\Eloquent\Model;

class SpotifyToken extends Model
{
    public $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'expires_at'=>'datetime'
    ];

    public function forSpotify(): AccessToken
    {
        return new AccessToken(
            token: $this->token,
            refresh: $this->refresh,
            scopes: $this->scope,
            expiry: $this->expires_at,
        );
    }
}
