<?php

namespace App\UseCases\Aggregate;

use App\Repositories\ArtistGenreRepository;
use App\Repositories\ArtistRepository;
use App\Services\AggregateService;
use App\Services\SpotifyApiService;
use Exception;

final class Track
{
    public function __construct(
        private SpotifyApiService $spotifyApiService,
        private AggregateService $aggregateService,
        private ArtistRepository $artistRepositories,
        private ArtistGenreRepository $artistGenreRepositories
    ) {
    }

    public function __invoke(int $userId): string
    {
        try {
            $myTopTrack = $this->spotifyApiService->getMyTop($userId, 'tracks');
        } catch (Exception $e) {
            // tokenリフレッシュ判定
            if ($this->spotifyApiService->checkRefreshToken($e, $userId)) {
                $this->__invoke($userId);
                return;
            }
            return;
        }

        // 必要なデータを配列に入れる
        // アーティスト、アルバムの登録も行う
        $track_array = $this->aggregateService->convertMyTopTrackToArrayBySpotifyApi($myTopTrack, $userId);





        # トラック登録更新
        $this->track_model->insert_update_artist($track_array);

        # TOP rate登録
        $this->top_track_model->set_top_track($track_array);
    }
}
