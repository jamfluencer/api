<?php

namespace App\Playback;

use App\Spotify\Authentication\AccessToken;
use App\Spotify\Authentication\RefreshToken;
use App\Spotify\Facades\Spotify;
use Database\Factories\SpotifyTokenFactory;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $refresh
 * @property string $token
 * @property DateTime $expires_at
 * @property string $scope
 */
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
        if ($this->expires_at < new DateTime) {
            $refreshed = Spotify::refreshToken(new RefreshToken($this->refresh));
            $this->update([
                'token' => $refreshed->token,
                'expires_at' => $refreshed->expiresAt,
                'refresh' => $refreshed->refresh,
            ]);
        }

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
