<?php

namespace App\Playback;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SpotifyAccount extends Model
{
    protected $keyType = 'string';
    public $fillable = [
        'country',
        'id',
        'display_name'
    ];

    public function token(): HasOne
    {
        return $this->hasOne(SpotifyToken::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
