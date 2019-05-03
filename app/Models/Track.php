<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Library\SpotifyApi;

/**
 * Class Track
 * @package App\Models
 * @property Artist $artist_model;
 * @property Album $album_model;
 * @property Artist_Genre $artist_genre_model
 * @property Artist_Genre_Map $artist_genre_map_model
 * @property SpotifyApi $spotify_api_library
 */
class Track extends Model
{
    protected $spotify_api_library;
    protected $table = 'track';
    protected $guarded = ['id'];
    protected $fillable = [
        'spotify_id',
        'artist_id',
        'album_id',
        'name',
        'url',
        'popularity',
        'track_number',
        'preview_url'
    ];
    public $timestamps = true;
    Private $now;

    public function __construct()
    {
        $this->spotify_api_library = new SpotifyApi;
        $this->now = date('Y-m-d H:i:s');
    }

    /**
     * Spotify IDからtrackが登録されているか調べる
     * @param $spotify_id
     * @return bool
     */
    public function exist_track($spotify_id)
    {
        $data = DB::table($this->table)->where('spotify_id', $spotify_id)->first();
        if ($data) {
            return true;
        }
        return false;
    }

    /**
     * Spotify IDからtrackを取得
     * @param $spotify_id
     * @return Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function get_track_by_spotify_id($spotify_id)
    {
        return DB::table($this->table)->where('spotify_id', $spotify_id)->first();
    }

    /**
     * track情報を成形する。
     * artist、albumの情報がなれけば追加する
     * @param $track
     * @param $user_id
     * @return array
     */
    public function set_track_array($track, $user_id)
    {
        # 初期化
        $artist_model = new Artist();
        $album_model = new Album();
        $artist_genre_model = new Artist_Genre();
        $artist_genre_map_model = new Artist_Genre_Map();

        $track_array = [];

        foreach ($track->items as $index => $row) {

            $artist_spotify_id = $row->artists[0]->id;
            $album_spotify_id = $row->album->id;

            $track_array[$index]['track']['spotify_id'] = $row->id;
            $track_array[$index]['track']['name'] = $row->name;
            $track_array[$index]['track']['url'] = $row->external_urls->spotify;
            $track_array[$index]['track']['popularity'] = $row->popularity;
            $track_array[$index]['track']['track_number'] = $row->track_number;
            $track_array[$index]['track']['preview_url'] = $row->preview_url;

            # アーティストの情報が無い場合は登録を行う
            if (!$artist_model->exist_artist($artist_spotify_id)) {

                # アーティスト情報をAPI経由で取得
                $artist = $this->spotify_api_library->get_artist_by_id($user_id, $artist_spotify_id);

                # 必要なデータを配列に入れる
                $artist_array = $artist_model->set_artist_array($artist);

                # ジャンル情報をまとめる
                $genre_array = $artist_genre_model->put_artist_genre_array($artist_array);

                # アーティスト登録更新
                $artist_model->insert_update_artist($artist_array);

                # ジャンル登録
                $artist_genre_model->insert_update_genre($genre_array);

                # ジャンルマッピングする
                $artist_genre_map_model->set_artist_genre_map($artist_array);
            }

            # アーティストIDをセット
            $track_array[$index]['track']['artist_id'] = $artist_model->get_artist_id_by_spotify_id($artist_spotify_id);


            # アルバムの情報が無い場合は登録を行う
            if (!$album_model->exist_album($album_spotify_id)) {

                # アルバム情報をAPI経由で取得
                $album = $this->spotify_api_library->get_album_by_id($user_id, $album_spotify_id);

                # 必要なデータを配列に入れる
                $album_array = $album_model->set_album_array($album, $track_array[$index]['track']['artist_id']);

                # アルバム情報更新
                $album_model->insert_album($album_array);
            }
            # アルバムIDをセット
            $track_array[$index]['track']['album_id'] = $album_model->get_album_id_by_spotify_id($album_spotify_id);
        }
        return $track_array;
    }

    /**
     * Trackがあれば更新、無ければ追加する
     * @param $track_array
     */
    public function insert_update_artist($track_array)
    {
        # トラック情報を登録
        foreach ($track_array as $key => $value) {
            if ($this->exist_track($value['track']['spotify_id'])) {
                # UPDATE
                $this->update_track($value['track']);
            } else {
                # INSERT
                $this->insert_track($value['track']);
            }
        }
    }

    /**
     * Track追加
     * @param $track
     */
    public function insert_track($track)
    {
        $track_object = new Track;
        $track_object->fill($track);
        $track_object->save();
    }

    /**
     * Track更新
     * @param $track
     */
    public function update_track($track)
    {
        $track_object = DB::table($this->table)->where('spotify_id', $track['spotify_id']);
        $track['updated_at'] = $this->now;
        $track_object->update($track);
    }

}
