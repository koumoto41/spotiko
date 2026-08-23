<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $table = 'artist';
    protected $guarded = ['id'];
    protected $fillable = ['spotify_id', 'name', 'url', 'popularity', 'img_640', 'img_320', 'img_160'];
    public $timestamps = true;
}
