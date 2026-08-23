<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistGenre extends Model
{
    protected $table = 'artist_genre';
    protected $guarded = ['id'];
    protected $fillable = ['name'];
    public $timestamps = true;
}
