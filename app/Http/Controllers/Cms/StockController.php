<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Presenters;

class StockController extends Controller
{
    private $stock_presenter;
    const LIST_LIMIT = 20;

    public function __construct()
    {
        $this->stock_presenter = new Presenters\Cms\StockPresenter();
    }

    public function top()
    {
        try {
            $my_album = $this->stock_presenter->get_my_album_list('', self::LIST_LIMIT, 0);
        } catch (\Exception $e) {
            return false;
        }

        $data['count'] = $my_album['count'];
        $data['table_view'] = $this->stock_presenter->get_table_view($my_album['list']);
        $data['paginame'] = $my_album['paginame'];

        return view('cms.stock.lists')->with($data);
    }

    public function form()
    {
        $data['albums'] = $this->stock_presenter->get_album_list('', 10000, 0);



        return view('cms.stock.form')->with($data);
    }
}
