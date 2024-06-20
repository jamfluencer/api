<?php

namespace App\Playback;

use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static TrackFactory factory($count = null, $state = [])
 */
class Track extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'spotify_tracks';

    protected $fillable = [
        'id',
    ];
}
