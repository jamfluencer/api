<?php

namespace App\Playback;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read Collection<Track> $tracks
 */
class Playlist extends Model
{
    protected $table = 'spotify_playlists';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'url',
        'snapshot',
    ];

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(
            Track::class,
            'spotify_playlist_tracks',
            'playlist_id',
            'track_id'
        )
            ->withPivot('added_by');
    }
}
