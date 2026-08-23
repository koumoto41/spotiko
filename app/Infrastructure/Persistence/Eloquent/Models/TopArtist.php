<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class TopArtist extends Model
{
    protected $table = 'top_artist';

    protected $guarded = ['id'];

    protected $fillable = ['artist_id', 'target_date', 'point', 'popularity'];

    public $timestamps = true;
}
