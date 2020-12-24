<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;
use App\Library\SpotifyApi;


/**
 * Class DisplayController
 * @package App\Http\Controllers
 * @property Models\Artist $artist_model
 * @property Models\Track $track_model
 * @property Models\Artist_Genre $artist_genre_model
 * @property Models\Artist_Genre_Map $artist_genre_map_model
 * @property Models\Top_Artist $top_artist_model
 * @property Models\Top_Track $top_track_model
 */
class DisplayController extends Controller
{
    protected $spotify_api_library;

    public function __construct(SpotifyApi $spotify_api_library)
    {
        $this->spotify_api_library = $spotify_api_library;

        $this->artist_model = new Models\Artist();
        $this->track_model = new Models\Track();
        $this->artist_genre_model = new Models\Artist_Genre();
        $this->artist_genre_map_model = new Models\Artist_Genre_Map();
        $this->top_artist_model = new Models\Top_Artist();
        $this->top_track_model = new Models\Top_Track();
    }

    public function page_top()
    {
        // アーティスト集計関係のデータを取得
        $aggregate_artist_date_list = $this->top_artist_model->get_aggregate_date();
        $data['display_artist_date'] = $this->top_artist_model->get_aggregate_date(true);

        // アーティストデータ取得
        $data['artist'] = $this->top_artist_model->get_top_artist_by_aggregate_date($data['display_artist_date']);

        // トラック集計関係のデータを取得
        $aggregate_track_date_list = $this->top_track_model->get_aggregate_date();
        $data['display_track_date'] = $this->top_track_model->get_aggregate_date(true);

        $data['track'] = $this->top_track_model->get_top_track_by_aggregate_date($data['display_track_date']);

        return view('top', $data);

    }

    public function line_test()
    {
        $value = $this->create_feature_playlist_content();

        var_dump($value);
    }

    private function create_feature_playlist_content()
    {
        $this->spotify_api_library->refresh_access_token(1);
        $feature_playlist = $this->spotify_api_library->get_feature_playlist(1, $limit = 50);

        $playlist_items = $feature_playlist->playlists->items;
        $result = '';
        $temp = [];

        foreach ($playlist_items as $key => $playlist) {
            $temp[$key]['name'] = $playlist->name;
            $temp[$key]['url'] = $playlist->external_urls->spotify;
        }
        shuffle($temp);

        foreach ($temp as $key => $value) {
            if ($key == 5) {
                break;
            }
            $result .= "{$value['name']}\n{$value['url']}\n\n";
        }
        return $result;
    }
}
