<?php

namespace App\Console\Commands\aggregate;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Library\SpotifyApi;
use Exception;
use App\Models;

/**
 * Class AggregateArtist
 * @package App\Http\Controllers
 * @property Request $request
 * @property SpotifyApi $spotify_api_library
 * @property Models\Artist $artist_model
 * @property Models\Track $track_model
 * @property Models\Artist_Genre $artist_genre_model
 * @property Models\Artist_Genre_Map $artist_genre_map_model
 * @property Models\Top_Artist $top_artist_model
 * @property Models\Top_Track $top_track_model
 */
class AggregateArtist extends Command
{
    protected $signature = 'command:AggregateArtist';
    protected $description = 'アーティスト集計';

    protected $spotify_api_library;
    const USER_ID = 1;

    public function __construct(SpotifyApi $spotify_api_library)
    {
        parent::__construct();

        $this->spotify_api_library = $spotify_api_library;
        $this->artist_model = new Models\Artist();
        $this->track_model = new Models\Track();
        $this->artist_genre_model = new Models\Artist_Genre();
        $this->artist_genre_map_model = new Models\Artist_Genre_Map();
        $this->top_artist_model = new Models\Top_Artist();
        $this->top_track_model = new Models\Top_Track();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $artist = $this->get_my_artists();
        if (!$artist) {
            echo '【アーティスト集計】集計に失敗しました';
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

        echo '【アーティスト集計】集計が終了しました';
    }

    /**
     * @return array|bool|object
     */
    private function get_my_artists()
    {
        try {
            return $this->spotify_api_library->get_my_top(self::USER_ID, $type = 'artists');
        } catch (Exception $e) {
            # tokenリフレッシュ判定
            if ($this->spotify_api_library->is_refresh_token($e, self::USER_ID)) {
                $this->get_my_artists();
                return false;
            }
            return false;
        }
    }
}
