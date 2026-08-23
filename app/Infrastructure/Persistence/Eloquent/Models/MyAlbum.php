<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class MyAlbum extends Model
{
    protected $table = 'my_album';

    protected $guarded = ['id'];

    protected $fillable = ['album_id'];

    public $timestamps = true;
}
