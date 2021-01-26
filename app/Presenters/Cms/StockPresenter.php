<?php

/**
 * Class StockPresenter
 */

namespace App\Presenters\Cms;

use App\Models;
use App\Presenters;
use App\Library\SpotifyApi;

class StockPresenter
{
    const TOP_TABLE_TH = ['画像', 'アルバム名', 'アーティスト名', '登録年月日'];
    const SPOTIFT_ALBUM_TABLE_TH = ['画像', 'アルバム名', 'アーティスト名', 'リリース年', '追加'];

    private $my_album_model;
    private $album_model;
    private $artist_model;
    private $artist_genre_model;
    private $artist_genre_map_model;
    private $table_presenter;
    private $spotify_api_library;

    public function __construct()
    {
        $this->spotify_api_library = new SpotifyApi();
        $this->my_album_model = new Models\My_Album();
        $this->album_model = new Models\Album();
        $this->artist_model = new Models\Artist();
        $this->artist_genre_model = new Models\Artist_Genre();
        $this->artist_genre_map_model = new Models\Artist_Genre_Map();
        $this->table_presenter = new Presenters\TablePresenter();
    }

    public function get_my_album_list($query, $type, $limit, $offset)
    {
        $obj_my_album = $this->my_album_model->get_my_album($query, $type, $limit, $offset);

        return [
            'count' => $obj_my_album->count(),
            'list' => $obj_my_album->get()->toArray(),
            'paginame' => $obj_my_album->paginate($limit)
        ];
    }

    public function search_album_from_spotify($query, $limit, $offset)
    {
        $albums = $this->spotify_api_library->search_item(1, $query, 'album', $limit, $offset);
        return $this->parse_spotify_album_data($albums);
    }

    private function parse_spotify_album_data($obj)
    {
        $result = [];
        foreach ($obj->albums->items as $item) {
            $result[] = [
                'album_name' => $item->name,
                'album_spotify_id' => $item->id,
                'release_date' => $item->release_date,
                'type' => $item->type,
                'artist_name' => $item->artists[0]->name,
                'artist_spotify_id' => $item->artists[0]->id,
                'image' => $item->images[0]->url ?? ''
            ];
        }
        return $result;
    }

    public function get_table_view($data, $type='top')
    {
        if (!$data) {
            return '';
        }

        if ($type == 'top') {
            $this->add_top_table_th();
            $this->add_top_table_td($data);
        } elseif($type == 'spotify') {
            $this->add_spotify_table_th();
            $this->add_spotify_table_td($data);
        }

        $table_view = $this->table_presenter->get_view();

        return $table_view;
    }

    public function add_top_table_th()
    {
        $this->table_presenter->set_th(self::TOP_TABLE_TH);
    }

    public function add_spotify_table_th()
    {
        $this->table_presenter->set_th(self::SPOTIFT_ALBUM_TABLE_TH);
    }

    public function add_top_table_td($data)
    {
        if (!$data) {
            return [];
        }

        foreach ($data as $row_number => $item) {

            $this->table_presenter->set_td($row_number, [
                view('cms.parts.image')->with(['url' => $item->img_64]),
                e($item->album_name),
                e($item->artist_name),
                e($item->created_at),
            ]);

        }
    }

    public function add_spotify_table_td($data)
    {
        if (!$data) {
            return [];
        }

        foreach ($data as $row_number => $item) {
            $this->table_presenter->set_td($row_number, [
                view('cms.parts.image')->with(['url' => $item['image']]),
                e($item['album_name']),
                e($item['artist_name']),
                e($item['release_date']),
                view('cms.parts.button_js')->with([
                    'text' => '追加',
                    'type' => 'danger',
                    'extra' => 'data-album_id="'.e($item['album_spotify_id']).'" data-artist_id="'.e($item['artist_spotify_id']).'"',
                    'class' => 'btn-register'
                ])
            ]);
        }
    }

    public function register_my_album($artist_spotify_id, $album_spotify_id)
    {
        if (!$this->artist_model->exist_artist($artist_spotify_id)) {
            // アーティスト情報の登録が無ければ追加する
            $artist = $this->spotify_api_library->get_artist_by_id(1, $artist_spotify_id);
            $artist_array = $this->artist_model->set_artist_array($artist);
            $genre_array = $this->artist_genre_model->put_artist_genre_array($artist_array);
            $this->artist_model->insert_update_artist($artist_array);
            $this->artist_genre_model->insert_update_genre($genre_array);
            $this->artist_genre_map_model->set_artist_genre_map($artist_array);
        }
        $register_artist_id = $this->artist_model->get_artist_id_by_spotify_id($artist_spotify_id);

        if (!$this->album_model->exist_album($artist_spotify_id)) {
            // アルバム情報の登録が無ければ追加する
            $album = $this->spotify_api_library->get_album_by_id(1, $album_spotify_id);
            $album_array = $this->album_model->set_album_array($album, $register_artist_id);
            $this->album_model->insert_album($album_array);
        }
        $register_album_id = $this->album_model->get_album_id_by_spotify_id($album_spotify_id);

        if ($this->my_album_model->exist_my_album($register_album_id)) {
            return;
        }

        $this->my_album_model->insert_my_album([
            'album_id' => $register_album_id,
        ]);

        return;
    }
}