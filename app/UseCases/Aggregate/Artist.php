<?php

namespace App\UseCases\Aggregate;

use App\Repositories\ArtistGenreRepository;
use App\Repositories\ArtistRepository;
use App\Services\AggregateService;
use App\Services\SpotifyApiService;
use Exception;

final class Artist
{
    public function __construct(
        private SpotifyApiService $spotifyApiService,
        private AggregateService $aggregateService,
        private ArtistRepository $artistRepositories,
        private ArtistGenreRepository $artistGenreRepositories
    ) {
    }

    public function __invoke(int $userId): void
    {
        try {
            $myTopArtist = $this->spotifyApiService->getMyTop($userId, 'artists');
        } catch (Exception $e) {
            // tokenリフレッシュ判定
            if ($this->spotifyApiService->checkRefreshToken($e, $userId)) {
                $this->__invoke($userId);
                return;
            }
            return;
        }

        // 必要なデータを配列に入れる
        $myTopArtistArray = $this->aggregateService->convertMyTopArtistToArrayBySpotifyApi($myTopArtist);

        // ジャンル情報をまとめる
        $genreArray = $this->aggregateService->putArtistGenreArray($myTopArtistArray);

        // アーティストupsert
        $this->artistRepositories->upsertArtist($myTopArtistArray);

        // ジャンルupsert
        $this->artistGenreRepositories->upsertGenre($genreArray);

        // ジャンルマッピングする
        $this->aggregateService->mapArtistGenre($myTopArtistArray);

        // TOP Rate登録
        $this->aggregateService->decideTopArtist($myTopArtistArray);
    }
}
