<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $table = 'album';

    protected $guarded = ['id'];

    protected $fillable = [
        'spotify_id', 'artist_id', 'name', 'url', 'release_date',
        'total_track', 'type', 'img_640', 'img_300', 'img_64',
    ];

    public $timestamps = true;
}
