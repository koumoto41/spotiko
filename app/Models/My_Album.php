<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class My_Album extends Model
{
    protected $table = 'my_album';
    protected $guarded = ['id'];
    protected $fillable = [];
    public $timestamps = true;
    Private $now;

    public function __construct()
    {
        $this->now = date('Y-m-d H:i:s');
    }

    public function exist_my_album($album_id)
    {
        return DB::table($this->table)->where('album_id', $album_id)->exists();
    }

    public function get_my_album($query, $type, $limit, $offset)
    {
        $getting_columns = [
            'album.name AS album_name',
            'artist.name AS artist_name',
            'album.img_640 AS img_640',
            'album.img_300 AS img_300',
            'album.img_64 AS img_64',
            'album.url AS url',
            'my_album.created_at AS created_at'
        ];

        $my_album_query = DB::table($this->table)
            ->select($getting_columns)
            ->Join('album', 'my_album.album_id', '=', 'album.id')
            ->Join('artist', 'album.artist_id', '=', 'artist.id')
            ->offset($offset)
            ->limit($limit);

        if ($type == 'album') {
            $my_album_query = $my_album_query->where('album.name', 'like', "%{$query}%");
        } elseif($type == 'artist') {
            $my_album_query = $my_album_query->where('artist.name', 'like', "%{$query}%");
        }

        return $my_album_query;
    }

    public function insert_my_album($my_album)
    {
        $my_album_object = new My_Album;
        $my_album_object->fill($my_album);
        $my_album_object->save();
    }

}
