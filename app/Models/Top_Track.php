<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Top_Track extends Model
{
    protected $table = 'top_track';
    protected $guarded = ['id'];
    protected $fillable = ['artist_id', 'track_id', 'album_id', 'target_date', 'point', 'popularity'];
    public $timestamps = true;
    Private $now;

    const RANKING_POINT = 50;

    public function __construct()
    {
        $this->now = date('Y-m-d H:i:s');
    }

    /**
     * Track Top Rateの登録
     * @param $artist_array
     */
    public function set_top_track($track_array)
    {
        # 初期化
        $track_model = new Track();
        $target_date = date('Y-m-d');

        # 同じ日付のデータがあったら削除する
        if ($this->exist_target_date_data($target_date)) {
            $this->delete_top_track_by_target_date($target_date);
        }

        foreach ($track_array as $index => $row) {

            # Track ID取得
            $track = $track_model->get_track_by_spotify_id($row['track']['spotify_id']);

            if (!$track) {
                continue;
            }

            $track_id = $track->id;
            $popularity = $track->popularity;
            $artist_id = $row['track']['artist_id'];
            $album_id = $row['track']['album_id'];
            $point = (int)self::RANKING_POINT - (int)$index;

            $this->insert_top_track($target_date, $artist_id, $album_id, $track_id, $popularity, $point);
        }
    }

    /**
     * 該当の日付のデータがあるか調べる
     * @param $target_date
     * @return bool
     */
    public function exist_target_date_data($target_date)
    {
        $data = DB::table($this->table)->where('target_date', $target_date)->first();
        if ($data) {
            return true;
        }
        return false;
    }

    /**
     * Top Rateのデータを登録
     * @param $target_date
     * @param $artist_id
     * @param $album_id
     * @param $track_id
     * @param $popularity
     * @param $point
     */
    public function insert_top_track($target_date, $artist_id, $album_id, $track_id, $popularity, $point)
    {
        $upd_data = [
            'target_date' => $target_date,
            'artist_id' => $artist_id,
            'album_id' => $album_id,
            'track_id' => $track_id,
            'popularity' => $popularity,
            'point' => $point,
        ];

        $track_object = new Top_Track;
        $track_object->fill($upd_data);
        $track_object->save();
    }

    /**
     * 該当の日付のデータを削除
     * @param $target_date
     */
    public function delete_top_track_by_target_date($target_date)
    {
        DB::table($this->table)->where('target_date', $target_date)->delete();
    }

}
