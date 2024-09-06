<?php

namespace App\Playback;

use App\Spotify\Album;
use Database\Factories\ArtistFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
{
    use HasFactory;

    public static string $factory = ArtistFactory::class;

    protected $table = 'spotify_artists';

    public $incrementing = false;

    protected $keyType = 'string';

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(
            Track::class,
            'spotify_track_artists'
        );
    }

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class);
    }
}