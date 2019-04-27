<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Artist;

class Top_Artist extends Model
{
    protected $table = 'top_artist';
    protected $guarded = ['id'];
    protected $fillable = ['artist_id', 'target_date', 'point', 'popularity'];
    public $timestamps = true;
    Private $now;

    const RANKING_POINT = 50;

    public function __construct()
    {
        $this->artist_model = new Artist();
        $this->now = date('Y-m-d H:i:s');
    }

    public function set_top_artist($artist_array)
    {
        $target_date = date('Y-m-d');

        if (!$this->exist_target_date_data($target_date)) {
            $this->delete_top_artist_by_target_date($target_date);
        }

        foreach ($artist_array as $index => $row) {

            $artist = $this->artist_model->get_artist_by_spotify_id($row['artist']['spotify_id']);

            if (!$artist) {
                continue;
            }

            $artist_id = $artist->id;
            $popularity = $artist->popularity;
            $point = (int)self::RANKING_POINT - (int)$index;

            $this->insert_top_artist($target_date, $artist_id, $popularity, $point);
        }
    }

    public function exist_target_date_data($target_date)
    {
        $data = DB::table($this->table)->where('target_date', $target_date)->first();

        if ($data) return true;
        return false;
    }

    public function insert_top_artist($target_date, $artist_id, $popularity, $point)
    {
        DB::table($this->table)->insert([
            'target_date' => $target_date,
            'artist_id' => $artist_id,
            'popularity' => $popularity,
            'point' => $point,
            'updated_at' => $this->now,
            'created_at' => $this->now
        ]);
    }

    public function delete_top_artist_by_target_date($target_date) {
        DB::table($this->table)->where('target_date', $target_date)->delete();
    }

}
