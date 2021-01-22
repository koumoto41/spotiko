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


//        $data['search_form_view'] = $this->load->view('parts/search_form', [
//            'search_forms' => $this->company_presenter->get_search_block_view()
//        ], true);
//
//        $this->load_view('company/list', $data);







        return view('cms.stock.lists')->with($data);
    }
}
