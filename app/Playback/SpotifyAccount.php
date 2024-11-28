<?php

namespace App\Playback;

use App\Models\User;
use App\Social\Kudos;
use Database\Factories\SpotifyAccountFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static SpotifyAccountFactory factory($count = null, $state = [])
 */
class SpotifyAccount extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    public $fillable = [
        'country',
        'id',
        'display_name',
    ];

    public function mappedDisplayName(): Attribute
    {
        return Attribute::make(get: fn () => "{$this->user->name} ({$this->display_name})");
    }

    public function token(): HasOne
    {
        return $this->hasOne(SpotifyToken::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kudos(): HasMany
    {
        return $this->hasMany(Kudos::class, 'for_spotify_account_id');
    }

    protected static function newFactory(): SpotifyAccountFactory
    {
        return SpotifyAccountFactory::new();
    }
}
