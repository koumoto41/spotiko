<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\SpotifyApi;
use Exception;
use App\Models\Artist;
use App\Models\Artist_Genre;
use App\Models\Artist_Genre_Map;
use App\Models\Top_Artist;

/**
 * Class ArtistController
 * @package App\Http\Controllers
 * @property Request $request
 * @property SpotifyApi $spotify_api_library
 * @property Artist $artist_model
 * @property Artist_Genre $artist_genre_model
 * @property Artist_Genre_Map $artist_genre_map_model
 * @property Top_Artist $top_artist_model
 */
class ArtistController extends Controller
{
    protected $spotify_api_library;
    const USER_ID = 1;

    public function __construct(SpotifyApi $spotify_api_library)
    {
        $this->spotify_api_library = $spotify_api_library;
        $this->artist_model = new Artist();
        $this->artist_genre_model = new Artist_Genre();
        $this->artist_genre_map_model = new Artist_Genre_Map();
        $this->top_artist_model = new Top_Artist();
    }


    public function set_top_artist() {

        try {
            $artist = $this->spotify_api_library->get_top_artist(self::USER_ID);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $code = $e->getCode();

            # tokenの期限切れなのでリフレッシュする
            if ($code == 401 && $message == 'The access token expired') {
                $this->spotify_api_library->refresh_access_token(self::USER_ID);
                $this->set_top_artist();
            }

            echo 'Message：'.$message.'<br>';
            echo 'Code：'.$code;
            return;
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

        # TOP rate登録
        $this->top_artist_model->set_top_artist($artist_array);

        echo '集計が終了しました';
    }

}
