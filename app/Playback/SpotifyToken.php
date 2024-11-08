<?php

namespace App\Playback;

use App\Spotify\AccessToken;
use Database\Factories\SpotifyTokenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotifyToken extends Model
{
    use HasFactory;

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
            expiry: $this->expires_at,
            refresh: $this->refresh,
            scopes: $this->scope,
        );
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SpotifyAccount::class);
    }

    protected static function newFactory(): SpotifyTokenFactory
    {
        return SpotifyTokenFactory::new();
    }
}
