<?php

namespace App\Presentation\Http\Controllers\Api;

use App\Application\Services\Cms\StockService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\StoreStockRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StockController extends Controller
{
    private const LIST_LIMIT = 20;

    public function __construct(private StockService $stockService) {}

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $query = (string) $request->query('query', '');
        $type = (string) $request->query('type', 'album');
        $offset = self::LIST_LIMIT * ($page - 1);

        $result = $this->stockService->getMyAlbumList($query, $type, self::LIST_LIMIT, $offset);

        return response()->json([
            'count' => $result['count'],
            'items' => $result['list'],
            'page' => $page,
            'limit' => self::LIST_LIMIT,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $query = (string) $request->query('query', '');
        $offset = self::LIST_LIMIT * ($page - 1);

        $items = $query !== '' ? $this->stockService->searchAlbumFromSpotify($query, self::LIST_LIMIT, $offset) : [];

        return response()->json(['items' => $items]);
    }

    public function store(StoreStockRequest $request): JsonResponse
    {
        $result = $this->stockService->registerMyAlbum(
            $request->validated('artist_id'),
            $request->validated('album_id')
        );

        return response()->json($result, $result['processing'] ? 201 : 200);
    }
}
