<?php

namespace App\Playback;

use App\Models\User;
use Database\Factories\SpotifyAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function token(): HasOne
    {
        return $this->hasOne(SpotifyToken::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): SpotifyAccountFactory
    {
        return SpotifyAccountFactory::new();
    }
}
