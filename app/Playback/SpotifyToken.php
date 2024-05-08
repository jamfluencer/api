<?php

namespace App\Playback;

use App\Models\User;
use App\Spotify\AccessToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotifyToken extends Model
{
    public $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
