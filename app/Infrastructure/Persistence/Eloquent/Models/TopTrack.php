<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class TopTrack extends Model
{
    protected $table = 'top_track';

    protected $guarded = ['id'];

    protected $fillable = ['artist_id', 'track_id', 'album_id', 'target_date', 'point', 'popularity'];

    public $timestamps = true;
}
