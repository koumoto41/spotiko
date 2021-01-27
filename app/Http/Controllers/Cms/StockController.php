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

    public function top(Request $request)
    {
        $data['page'] = (int)$request->input('page') ? (int)$request->input('page') : 1;
        $offset = self::LIST_LIMIT * ($data['page'] - 1);

        // パラメータ管理
        $data['params'] = $this->stock_presenter->make_params($request);

        try {
            $my_album = $this->stock_presenter->get_my_album_list($data['params']['query'], 'album', self::LIST_LIMIT, $offset);
        } catch (\Exception $e) {
            return false;
        }

        $data['count'] = $my_album['count'];
        $data['table_view'] = $this->stock_presenter->get_table_view($my_album['list']);
        $data['paginame'] = $my_album['paginame'];

        return view('cms.stock.lists')->with($data);
    }

    public function form(Request $request)
    {
        $data['page'] = $request->input('page') ?? 1;
        $data['query'] = $request->input('query');
        $offset = self::LIST_LIMIT * ($data['page'] - 1);

        $result = [];
        if ($data['query']) {
            $result = $this->stock_presenter->search_album_from_spotify($data['query'], self::LIST_LIMIT, $offset);
        }

        $data['count'] = count($result);
        $data['table_view'] = $this->stock_presenter->get_table_view($result, 'spotify');

        return view('cms.stock.form')->with($data);
    }

    public function register(Request $request)
    {
        $artist_id = $request->input('artist_id');
        $album_id = $request->input('album_id');

        try {
            $result= $this->stock_presenter->register_my_album($artist_id, $album_id);
        } catch (\Exception $e) {
            return response(json_encode(['message' => $e->getMessage()]), 400)
                ->header('Content-Type', 'application/json');
        }

        return response(json_encode($result), 200)
            ->header('Content-Type', 'application/json');
    }
}
