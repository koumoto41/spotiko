<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Album extends Model
{
    protected $table = 'album';
    protected $guarded = ['id'];
    protected $fillable = [];
    public $timestamps = true;
    Private $now;

    public function __construct()
    {
        $this->now = date('Y-m-d H:i:s');
    }

    /**
     * Spotify IDからAlbumが登録されているか調べる
     * @param $spotify_id
     * @return bool
     */
    public function exist_album($spotify_id)
    {
        $data = DB::table($this->table)->where('spotify_id', $spotify_id)->first();
        if ($data) {
            return true;
        }
        return false;
    }

    /**
     * Spotify IDからAlbum IDを取得する
     * @param $spotify_id
     * @return mixed|string
     */
    public function get_album_id_by_spotify_id($spotify_id)
    {
        $data = DB::table($this->table)->where('spotify_id', $spotify_id)->first();
        if ($data) {
            return $data->id;
        }
        return '';
    }

    /**
     * Album情報を成形する
     * @param $album
     * @param $artist_id
     * @return array
     */
    public function set_album_array($album, $artist_id)
    {

        $album_array = [];

        $album_array['spotify_id'] = $album->id;
        $album_array['artist_id'] = $artist_id;
        $album_array['name'] = $album->name;
        $album_array['url'] = $album->external_urls->spotify;
        $album_array['release_date'] = $album->release_date;
        $album_array['total_track'] = $album->total_tracks;
        $album_array['type'] = $album->album_type;

        # 画像
        $album_array['img_640'] = '';
        $album_array['img_300'] = '';
        $album_array['img_64'] = '';

        foreach ($album->images as $image) {
            # 画像の登録はこのタイプに限定
            if ($image->height == '640' || $image->height == '300' || $image->height == '64') {
                $album_array['img_' . $image->height] = $image->url;
            }
        }

        return $album_array;
    }

    /**
     * Album追加
     * @param $album
     */
    public function insert_album($album)
    {
        $album_object = new Album;
        $album_object->fill($album);
        $album_object->save();
    }

    /**
     * アルバム一覧を取得
     * @param $params
     * @param $limit
     * @param $offset
     * @return \Illuminate\Support\Collection
     */
    public function get_album_list($params, $limit, $offset)
    {
        $getting_columns = [
            'album.id AS album_id',
            'album.name AS album_name',
            'artist.name AS artist_name',
        ];

        return DB::table($this->table)
            ->select($getting_columns)
            ->Join('artist', 'album.artist_id', '=', 'artist.id')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }
}
