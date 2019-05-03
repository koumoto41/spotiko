<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Artist
 * @package App\Models
 */
class Artist extends Model
{
    protected $table = 'artist';
    protected $guarded = ['id'];
    protected $fillable = ['spotify_id', 'name', 'url', 'popularity', 'img_640', 'img_320', 'img_160'];
    public $timestamps = true;
    Private $now;

    public function __construct()
    {
        $this->now = date('Y-m-d H:i:s');
    }

    /**
     * spotifyIDでアーティストが登録されているか調べる
     * @param $spotify_id
     * @return bool
     */
    public function exist_artist($spotify_id)
    {

        $data = DB::table($this->table)->where('spotify_id', $spotify_id)->first();

        if ($data) {
            return true;
        }
        return false;
    }

    public function get_artist_id_by_spotify_id($spotify_id)
    {
        $data = DB::table($this->table)->where('spotify_id', $spotify_id)->first();

        if ($data) {
            return $data->id;
        }
        return '';
    }

    public function get_artist_by_spotify_id($spotify_id)
    {
        return DB::table($this->table)->where('spotify_id', $spotify_id)->first();
    }

    /**
     * API:getMyTopで取得したartist情報を配列に変換
     * @param $artist
     * @return array
     */
    public function set_artist_array($artist)
    {

        $artist_array = [];

        if (isset($artist->items)) {
            $artist_items = $artist->items;
        } else {
            $artist_items[] = $artist;
        }

        foreach ($artist_items as $index => $row) {
            $artist_array[$index]['artist']['spotify_id'] = $row->id;
            $artist_array[$index]['artist']['name'] = $row->name;
            $artist_array[$index]['artist']['url'] = $row->external_urls->spotify;
            $artist_array[$index]['artist']['popularity'] = $row->popularity;

            # 画像
            $artist_array[$index]['artist']['img_640'] = '';
            $artist_array[$index]['artist']['img_320'] = '';
            $artist_array[$index]['artist']['img_160'] = '';

            foreach ($row->images as $image) {
                # 画像の登録はこのタイプに限定
                if ($image->height == '640' || $image->height == '320' || $image->height == '160') {
                    $artist_array[$index]['artist']['img_' . $image->height] = $image->url;
                }
            }

            # ジャンル
            foreach ($row->genres as $genre) {
                $artist_array[$index]['genre'][] = $genre;
            }
        }
        return $artist_array;
    }


    /**
     * Artistがあれば更新、無ければ追加する
     * @param $artist_array
     */
    public function insert_update_artist($artist_array)
    {
        # アーティスト情報を登録
        foreach ($artist_array as $key => $value) {
            if ($this->exist_artist($value['artist']['spotify_id'])) {
                # UPDATE
                $this->update_artist($value['artist']);
            } else {
                # INSERT
                $this->insert_artist($value['artist']);
            }
        }
    }

    /**
     * Artist追加
     * @param $artist
     */
    public function insert_artist($artist)
    {
        $artist_object = new Artist;
        $artist_object->fill($artist);
        $artist_object->save();
    }

    /**
     * Artist更新
     * @param $artist
     */
    public function update_artist($artist)
    {
        $artist_object = DB::table($this->table)->where('spotify_id', $artist['spotify_id']);
        $artist['updated_at'] = $this->now;
        $artist_object->update($artist);
    }

}