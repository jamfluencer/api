<?php

namespace App\Playback;

use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static TrackFactory factory($count = null, $state = [])
 *
 * @property-read Playlist $first_occurrence
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
        'name',
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

    public function firstOccurrence(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->playlists()->orderByPivot('created_at')->first()
        );
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'spotify_track_artists',
            'track_id', 'artist_id');
    }
}
