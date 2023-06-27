<?php

namespace App\UseCases\Aggregate;

use App\Domains\SpotifyApi;
use App\Domains\Aggregate;
use App\Repositories\ArtistRepositories;
use App\Repositories\ArtistGenreRepositories;
use Exception;

final class Artist
{
    public function __construct(
        private SpotifyApi $spotifyApi,
        private Aggregate $aggregateDomain,
        private ArtistRepositories $artistRepositories,
        private ArtistGenreRepositories $artistGenreRepositories
    ) {
    }

    public function __invoke(int $userId): string
    {
        try {
            $myTopArtist = $this->spotifyApi->getMyTop($userId, 'artists');
        } catch (Exception $e) {
            // tokenリフレッシュ判定
            if ($this->spotifyApi->checkRefreshToken($e, $userId)) {
                $this->__invoke($userId);
                return;
            }
            return;
        }

        // 必要なデータを配列に入れる
        $myTopArtistArray = $this->aggregateDomain->convertMyTopArtistToArrayBySpotifyApi($myTopArtist);

        // ジャンル情報をまとめる
        $genreArray = $this->aggregateDomain->putArtistGenreArray($myTopArtistArray);

        // アーティストupsert
        $this->artistRepositories->upsertArtist($myTopArtistArray);

        // ジャンルupsert
        $this->artistGenreRepositories->upsertGenre($genreArray);

        // ジャンルマッピングする
        $this->aggregateDomain->mapArtistGenre($myTopArtistArray);

        // TOP Rate登録
        $this->aggregateDomain->decideTopArtist($myTopArtistArray);
    }
}
