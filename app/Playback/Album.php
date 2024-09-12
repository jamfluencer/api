<?php

namespace App\Playback;

use Database\Factories\AlbumFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Album extends Model
{
    use HasFactory;

    public static string $factory = AlbumFactory::class;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'spotify_albums';
    
    protected $fillable = [
        'id',
        'name',
        'uri'
    ];

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'spotify_album_tracks', 'album_id', 'track_id');
    }
}
