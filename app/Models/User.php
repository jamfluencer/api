<?php

namespace App\Models;

use App\Playback\SpotifyAccount;
use App\Playback\SpotifyToken;
use App\Social\Kudos;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static UserFactory factory($count = null, $state = [])
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'spotifyToken',
    ];

    protected $appends = [
        'has_spotify',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function spotifyToken(): HasOneThrough
    {
        return $this->hasOneThrough(SpotifyToken::class, SpotifyAccount::class);
    }

    public function spotifyAccounts(): HasMany
    {
        return $this->hasMany(SpotifyAccount::class);
    }

    protected function hasSpotify(): Attribute
    {
        return new Attribute(
            get: fn () => (bool) $this->spotifyToken
        );
    }

    public function kudos(): HasMany
    {
        return $this->hasMany(Kudos::class, 'for_user_id');
    }
}
