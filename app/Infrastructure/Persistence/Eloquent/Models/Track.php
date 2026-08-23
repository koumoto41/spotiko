<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $table = 'track';

    protected $guarded = ['id'];

    protected $fillable = [
        'spotify_id', 'artist_id', 'album_id', 'name', 'url',
        'popularity', 'track_number', 'preview_url',
    ];

    public $timestamps = true;
}
