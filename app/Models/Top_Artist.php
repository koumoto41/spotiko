<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Top_Artist extends Model
{
    protected $table = 'top_artist';
    protected $guarded = ['id'];
    protected $fillable = ['artist_id', 'target_date', 'point', 'popularity'];
    public $timestamps = true;
    private $now;

    const RANKING_POINT = 50;

    public function __construct()
    {
        $this->now = date('Y-m-d H:i:s');
    }

    /**
     * Artist Top Rateの登録
     * @param $artist_array
     */
    public function set_top_artist($artist_array)
    {
        # 初期化
        $artist_model = new Artist();
        $target_date = date('Y-m-d');

        # 同じ日付のデータがあったら削除する
        if ($this->exist_target_date_data($target_date)) {
            $this->delete_top_artist_by_target_date($target_date);
        }

        foreach ($artist_array as $index => $row) {

            # Artist取得
            $artist = $artist_model->get_artist_by_spotify_id($row['artist']['spotify_id']);

            if (!$artist) {
                continue;
            }

            $artist_id = $artist->id;
            $popularity = $artist->popularity;
            $point = (int)self::RANKING_POINT - (int)$index;

            $this->insert_top_artist($target_date, $artist_id, $popularity, $point);
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
     * Artist Top Rate登録
     * @param $target_date
     * @param $artist_id
     * @param $popularity
     * @param $point
     */
    public function insert_top_artist($target_date, $artist_id, $popularity, $point)
    {
        $upd_data = [
            'target_date' => $target_date,
            'artist_id' => $artist_id,
            'popularity' => $popularity,
            'point' => $point,
        ];

        $track_object = new Top_Artist;
        $track_object->fill($upd_data);
        $track_object->save();
    }

    /**
     * 該当の日付のデータを削除
     * @param $target_date
     */
    public function delete_top_artist_by_target_date($target_date)
    {
        DB::table('top_artist')->where('target_date', $target_date)->delete();
    }

    /**
     * 集計を取ってる日付を表示
     * @return mixed
     */
    public function get_aggregate_date($first = false)
    {
        $aggregate_date = DB::table($this->table)
            ->groupBy('target_date')
            ->latest('target_date')
            ->pluck('target_date');

        if ($first) {
            return $aggregate_date->first();
        }
        return $aggregate_date;
    }

    public function get_top_artist_by_aggregate_date($aggregate_date)
    {
        return DB::table($this->table)
            ->join('artist', 'top_artist.artist_id', '=', 'artist.id')
            ->where('target_date', $aggregate_date)
            ->latest('target_date')
            ->get();
    }
}
