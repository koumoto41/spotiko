<?php

namespace App\Console\Commands\aggregate;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Library\SpotifyApi;
use Exception;
use App\Models\Artist;
use App\Models\Track;
use App\Models\Artist_Genre;
use App\Models\Artist_Genre_Map;
use App\Models\Top_Artist;
use App\Models\Top_Track;

/**
 * Class AggregateTrack
 * @package App\Http\Controllers
 * @property Request $request
 * @property SpotifyApi $spotify_api_library
 * @property Artist $artist_model
 * @property Track $track_model
 * @property Artist_Genre $artist_genre_model
 * @property Artist_Genre_Map $artist_genre_map_model
 * @property Top_Artist $top_artist_model
 * @property Top_Track $top_track_model
 */
class AggregateTrack extends Command
{
    protected $signature = 'command:AggregateTrack';
    protected $description = '曲目集計';

    protected $spotify_api_library;
    const USER_ID = 1;

    public function __construct(SpotifyApi $spotify_api_library)
    {
        parent::__construct();

        $this->spotify_api_library = $spotify_api_library;
        $this->artist_model = new Artist();
        $this->track_model = new Track();
        $this->artist_genre_model = new Artist_Genre();
        $this->artist_genre_map_model = new Artist_Genre_Map();
        $this->top_artist_model = new Top_Artist();
        $this->top_track_model = new Top_Track();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $artist = $this->get_my_artist();
        if (!$artist) {
            echo '【曲目集計】アーティストの集計に失敗しました';
            return false;
        }

        # 必要なデータを配列に入れる
        $artist_array = $this->artist_model->set_artist_array($artist);

        # ジャンル情報をまとめる
        $genre_array = $this->artist_genre_model->put_artist_genre_array($artist_array);

        # アーティスト登録更新
        $this->artist_model->insert_update_artist($artist_array);

        # ジャンル登録
        $this->artist_genre_model->insert_update_genre($genre_array);

        # ジャンルマッピングする
        $this->artist_genre_map_model->set_artist_genre_map($artist_array);

        # TOP Rate登録
        $this->top_artist_model->set_top_artist($artist_array);

        echo '【曲目集計】集計が終了しました。';
    }

    /**
     * @return array|bool|object
     */
    public function get_my_artist()
    {
        try {
            return $this->spotify_api_library->get_my_top(self::USER_ID, $type = 'artists');
        } catch (Exception $e) {
            # tokenリフレッシュ判定
            if ($this->spotify_api_library->is_refresh_token($e, self::USER_ID)) {
                $this->get_my_artist();
                return false;
            }
            return false;
        }
    }
}
