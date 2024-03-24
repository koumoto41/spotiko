<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Token
 * @package App\Models
 */
class Token extends Model
{
    protected $table = 'token';
    protected $guarded = ['id'];
    protected $fillable = ['use_id', 'token'];
    public $timestamps = true;
}
