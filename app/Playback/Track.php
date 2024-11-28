<?php

namespace App\Playback;

use App\Social\Kudos;
use Carbon\CarbonInterval;
use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'url',
    ];

    protected $appends = [
        'album',
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

    public function duration(): Attribute
    {
        return Attribute::make(get: fn ($value) => CarbonInterval::create(seconds: $value / CarbonInterval::getMillisecondsPerSecond())->forHumans());
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

    public function album(): Attribute
    {
        return Attribute::make(get: fn () => $this->albums->loadMissing('images')->first());
    }

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class, 'spotify_album_tracks', 'track_id', 'album_id');
    }

    public function kudos(): HasMany
    {
        return $this->hasMany(Kudos::class, 'track_id');
    }
}
