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
    const SPOTIFT_ALBUM_TABLE_TH = ['画像', 'アルバム名', 'アーティスト名', 'リリース年'];

    private $my_album_model;
    private $album_model;
    private $table_presenter;
    private $spotify_api_library;

    public function __construct()
    {
        $this->spotify_api_library = new SpotifyApi();
        $this->my_album_model = new Models\My_Album();
        $this->album_model = new Models\Album();
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
                'spotify_id' => $item->id,
                'release_date' => $item->release_date,
                'type' => $item->type,
                'artist_name' => $item->artists[0]->name ?? '',
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
            ]);
        }
    }
}