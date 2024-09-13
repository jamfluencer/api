<?php

namespace App\Playback;

use Database\Factories\ImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    public static string $factory = ImageFactory::class;

    protected $table = 'spotify_images';

    protected $fillable = [
        'url',
        'width',
        'height',
    ];
}
