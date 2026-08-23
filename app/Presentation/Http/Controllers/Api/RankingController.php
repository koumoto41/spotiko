<?php

namespace App\Presentation\Http\Controllers\Api;

use App\Domain\TopArtist\Repositories\TopArtistRepositoryInterface;
use App\Domain\TopTrack\Repositories\TopTrackRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class RankingController extends Controller
{
    public function __invoke(
        TopArtistRepositoryInterface $topArtistRepository,
        TopTrackRepositoryInterface $topTrackRepository
    ): JsonResponse {
        $artistDate = $topArtistRepository->getLatestAggregateDate();
        $trackDate = $topTrackRepository->getLatestAggregateDate();

        return response()->json([
            'artists' => [
                'aggregate_date' => $artistDate,
                'items' => $artistDate ? $topArtistRepository->getTopArtistByAggregateDate($artistDate) : [],
            ],
            'tracks' => [
                'aggregate_date' => $trackDate,
                'items' => $trackDate ? $topTrackRepository->getTopTrackByAggregateDate($trackDate) : [],
            ],
        ]);
    }
}
