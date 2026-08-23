<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistGenreMap extends Model
{
    protected $table = 'artist_genre_map';

    protected $guarded = ['id'];

    protected $fillable = ['artist_id', 'genre_id'];

    public $timestamps = true;
}
