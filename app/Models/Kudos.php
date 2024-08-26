<?php

namespace App\Models;

use App\Playback\Playlist;
use App\Playback\SpotifyAccount;
use App\Playback\Track;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kudos extends Model
{
    use HasFactory;

    public $fillable = [
        'track_id',
        'playlist_id',
        'for_user_id',
        'from_user_id',
        'for_spotify_account_id',
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }

    public function forSpotifyAccount(): BelongsTo
    {
        return $this->belongsTo(SpotifyAccount::class, 'for_spotify_account_id');
    }

    public function forUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'for_user_id');
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}