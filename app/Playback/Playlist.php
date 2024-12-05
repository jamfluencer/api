<?php

namespace App\Playback;

use Database\Factories\PlaylistFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read Collection<Track> $tracks
 */
class Playlist extends Model
{
    use HasFactory;

    public static string $factory = PlaylistFactory::class;

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
            ->withPivot('added_by')
            ->withTimestamps();
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class);
    }

    public function kudos(): Attribute
    {
        $id = $this->id;
        $sum = $this->tracks->sum(fn (Track $track) => $track->kudos()->count());

        return new Attribute(get: fn () => $sum);
    }

    public function duration(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tracks->sum(
                fn (Track $track) => $track->getRawOriginal('duration')
            ),
        );
    }
}
