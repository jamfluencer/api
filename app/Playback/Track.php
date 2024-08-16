<?php

namespace App\Playback;

use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static TrackFactory factory($count = null, $state = [])
 */
class Track extends Model
{
    use HasFactory;

    public static string $factory = TrackFactory::class;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'spotify_tracks';

    protected $fillable = [
        'id',
    ];

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(
            Playlist::class,
            'spotify_playlist_tracks',
            'track_id',
            'playlist_id'
        )
            ->withPivot('added_by')
            ->withTimestamps();
    }
}
