<?php

namespace App\Presentation\Http\Controllers\Api;

use App\Application\UseCases\Aggregation\AggregateArtistUseCase;
use App\Application\UseCases\Aggregation\AggregateTrackUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class AggregationController extends Controller
{
    private const USER_ID = 1;

    public function artist(AggregateArtistUseCase $aggregateArtist): JsonResponse
    {
        $aggregateArtist(self::USER_ID);

        return response()->json(['message' => 'アーティスト集計が終了しました']);
    }

    public function track(AggregateTrackUseCase $aggregateTrack): JsonResponse
    {
        $aggregateTrack(self::USER_ID);

        return response()->json(['message' => '曲目集計が終了しました']);
    }
}
