<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'token';

    protected $guarded = ['id'];

    protected $fillable = ['user_id', 'token', 'refresh_token'];

    public $timestamps = true;
}
