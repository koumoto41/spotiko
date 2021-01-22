<?php

/**
 * Class StockPresenter
 */

namespace App\Presenters\Cms;

use App\Models\My_Album;
use App\Presenters;

class StockPresenter
{
    const LIST_TABLE_TH = ['画像', 'アルバム名', 'アーティスト名', '登録年月日'];

    private $my_album_model;
    private $table_presenter;

    public function __construct()
    {
        $this->my_album_model = new My_Album();
        $this->table_presenter = new Presenters\TablePresenter();
    }

    public function get_my_album_list($query, $limit, $offset)
    {
        $obj_my_album = $this->my_album_model->get_my_album($query, 'album', $limit, $offset);

        return [
            'count' => $obj_my_album->count(),
            'list' => $obj_my_album->get()->toArray(),
            'paginame' => $obj_my_album->paginate($limit)
        ];
    }

    public function get_table_view($data)
    {
        if (!$data) {
            return '';
        }

        $this->add_top_table_th();
        $this->add_top_table_td($data);

        $table_view = $this->table_presenter->get_view();

        return $table_view;
    }

    public function add_top_table_th()
    {
        $this->table_presenter->set_th(self::LIST_TABLE_TH);
    }

    public function add_top_table_td($data)
    {
        if (!$data) {
            return [];
        }

        foreach ($data as $row_number => $item) {


        }
    }
}