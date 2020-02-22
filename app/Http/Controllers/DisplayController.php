<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;


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
    public function __construct()
    {
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
        $aggregate_artist_date_first = $this->top_artist_model->get_aggregate_date(true);

        // アーティストデータ取得
        $top_artist_list = $this->top_artist_model->get_top_artist_by_aggregate_date($aggregate_artist_date_first);







        return view('top', ['artist' => $top_artist_list]);

    }









}
