<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Cms\StockService;

class StockController extends Controller
{
    private const LIST_LIMIT = 20;
    private const SEARCH_TYPE_LIST = ['artist' => 'アーティスト', 'album' => 'アルバム'];

    public function __construct(private StockService $stockService)
    {
    }

    public function getIndex()
    {
        $page = request('page', '1');
        $offset = self::LIST_LIMIT * ($page - 1);
        $query = request('query', '');
        $type = request('type', 'album');
        $data['params'] = ['type' => $type, 'query' => $query];

        // 検索フォーム
        $data['form']['type'] = self::SEARCH_TYPE_LIST;

        try {
            $myAlbum = $this->stockService->getMyAlbumList($query, $type, self::LIST_LIMIT, $offset);
        } catch (\Exception $e) {
            return false;
        }

        $data['count'] = $myAlbum['count'];
        $data['table_view'] = $this->stockService->getTableView($myAlbum['list']);
        $data['paginate'] = $myAlbum['paginate'];

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
