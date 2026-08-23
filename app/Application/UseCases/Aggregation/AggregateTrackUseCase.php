<?php

namespace App\Application\UseCases\Aggregation;

use App\Application\Services\AggregateService;
use App\Domain\Spotify\SpotifyClientInterface;
use App\Domain\Track\Repositories\TrackRepositoryInterface;
use Exception;

final class AggregateTrackUseCase
{
    public function __construct(
        private SpotifyClientInterface $spotifyClient,
        private AggregateService $aggregateService,
        private TrackRepositoryInterface $trackRepository
    ) {
    }

    public function __invoke(int $userId): void
    {
        try {
            $myTopTrack = $this->spotifyClient->getMyTop($userId, 'tracks');
        } catch (Exception $e) {
            if ($this->spotifyClient->checkRefreshToken($e, $userId)) {
                $this->__invoke($userId);
            }
            return;
        }

        // アーティスト・アルバムの登録も行いつつ配列に変換
        $trackArray = $this->aggregateService->convertMyTopTrackToArrayBySpotifyApi($myTopTrack, $userId);

        $this->trackRepository->upsertTrack($trackArray);
        $this->aggregateService->decideTopTrack($trackArray);
    }
}
