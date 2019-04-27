<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Artist_Genre extends Model
{
    protected $table = 'artist_genre';
    protected $guarded = ['id'];
    protected $fillable = ['name'];
    public $timestamps = true;

    /**
     * アーティストのジャンル情報をジャンルだけにまとめる
     * @param $artist_array
     * @return array
     */
    public function put_artist_genre_array($artist_array)
    {
        $genre_array = [];

        foreach ($artist_array as $row) {

            if (!isset($row['genre'])) {
                continue;
            }

            foreach ($row['genre'] as $genre) {

                if (!array_search($genre, $genre_array)) {
                    $genre_array[] = $genre;
                }
            }
        }
        return $genre_array;
    }

    /**
     * ジャンル名で登録されているか調べる
     * @param $genre_name
     * @return bool
     */
    public function exist_genre_name($genre_name)
    {
        $data = DB::table($this->table)->where('name', $genre_name)->first();
        if ($data) return true;
        return false;
    }

    public function get_genre_id_by_genre_name($genre_name)
    {
        $data = DB::table($this->table)->where('name', $genre_name)->first();

        if ($data) {
            return $data->id;
        }
        return '';
    }

    /**
     * ジャンルを登録
     * @param $genre_name
     */
    public function insert_genre($genre_name)
    {
        $genre_object = new Artist_Genre;
        $genre_object->name = $genre_name;
        $genre_object->save();
    }

    /**
     * データがなかったら登録する
     * @param $genre_array
     */
    public function insert_update_genre($genre_array)
    {
        foreach ($genre_array as $row) {
            if (!$this->exist_genre_name($row)) {
                $this->insert_genre($row);
            }
        }
    }
}