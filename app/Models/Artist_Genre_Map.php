<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Artist;
use App\Models\Artist_Genre;

class Artist_Genre_Map extends Model
{
    protected $table = 'artist_genre_map';
    protected $guarded = ['id'];
    protected $fillable = ['artist_id', 'genre_id'];
    public $timestamps = true;
    Private $now;

    public function __construct()
    {
        $this->artist_model = new Artist();
        $this->artist_genre_model = new Artist_Genre();
        $this->now = date('Y-m-d H:i:s');
    }

    public function set_artist_genre_map($artist_array)
    {
        foreach ($artist_array as $row) {
            if (!isset($row['genre'])) {
                continue;
            }

            $artist = $row['artist'];
            $genre_array = $row['genre'];

            $artist_id = $this->artist_model->get_artist_id_by_spotify_id($artist['spotify_id']);
            if (!$artist_id) {
                continue;
            }

            foreach ($genre_array as $genre) {
                $genre_id = $this->artist_genre_model->get_genre_id_by_genre_name($genre);

                if (!$this->exist_artist_genre_map($artist_id, $genre_id)) {
                    $this->insert_artist_genre_map($artist_id, $genre_id);
                }
            }
        }
    }

    public function exist_artist_genre_map($artist_id, $genre_id)
    {
        $query = DB::table($this->table);
        $query->where('artist_id', $artist_id);
        $query->where('genre_id', $genre_id);
        $data = $query->first();

        if ($data) return true;
        return false;
    }

    /**
     * @param $artist_id
     * @param $genre_id
     */
    public function insert_artist_genre_map($artist_id, $genre_id)
    {
        DB::table($this->table)->insert([
            'artist_id' => $artist_id,
            'genre_id' => $genre_id,
            'updated_at' => $this->now,
            'created_at' => $this->now
        ]);
    }

}
