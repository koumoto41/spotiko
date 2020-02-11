<?php

namespace App\Console\Commands\aggregate;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Library\SpotifyApi;
use Exception;
use App\Models;

/**
 * Class AggregateTrack
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
        $tracks = $this->get_my_tracks();
        if (!$tracks) {
            echo '【曲目集計】集計に失敗しました';
            return false;
        }

        # アーティスト、アルバムの登録も行う
        $track_array = $this->track_model->set_track_array($tracks, self::USER_ID);

        # トラック登録更新
        $this->track_model->insert_update_artist($track_array);

        # TOP rate登録
        $this->top_track_model->set_top_track($track_array);

        echo '【曲目集計】集計が終了しました。';
    }

    /**
     * @return array|bool|object
     */
    public function get_my_tracks()
    {
        try {
            return $this->spotify_api_library->get_my_top(self::USER_ID, $type = 'tracks');
        } catch (Exception $e) {
            # tokenリフレッシュ判定
            if ($this->spotify_api_library->is_refresh_token($e, self::USER_ID)) {
                $this->get_my_tracks();
                return false;
            }
            return false;
        }
    }
}
