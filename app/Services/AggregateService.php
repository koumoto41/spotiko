<?php

namespace App\Services;

use App\Repositories\ArtistGenreMapRepository;
use App\Repositories\ArtistGenreRepository;
use App\Repositories\ArtistRepository;
use App\Repositories\TopArtistRepository;

final class AggregateService
{
    // 登録許可画像サイズ
    public const ALLOWED_IMAGE_WIDTH = ['640', '320', '160'];
    public const RANKING_POINT = 50;

    public function __construct(
        private ArtistRepository $artistRepository,
        private ArtistGenreRepository $artistGenreRepository,
        private ArtistGenreMapRepository $artistGenreMapRepository,
        private TopArtistRepository $topArtistRepository
    ) {
    }

    /**
     * SpotifyAPI:getMyTopで取得したartist情報を配列に変換
     * @param $myTopArtist
     * @return array
     */
    public function convertMyTopArtistToArrayBySpotifyApi($myTopArtist): array
    {
        if (isset($myTopArtist->items)) {
            $artistItems = $myTopArtist->items;
        } else {
            $artistItems[] = $myTopArtist;
        }

        $artistArray = [];
        foreach ($artistItems as $index => $row) {
            $artistArray[$index]['artist']['spotify_id'] = $row->id;
            $artistArray[$index]['artist']['name'] = $row->name;
            $artistArray[$index]['artist']['url'] = $row->external_urls->spotify;
            $artistArray[$index]['artist']['popularity'] = $row->popularity;

            // 画像
            $artistArray[$index]['artist']['img_640'] = '';
            $artistArray[$index]['artist']['img_320'] = '';
            $artistArray[$index]['artist']['img_160'] = '';

            foreach ($row->images as $image) {
                // 画像の登録はこのタイプに限定
                if (in_array($image->height, self::ALLOWED_IMAGE_WIDTH, true)) {
                    $artistArray[$index]['artist']['img_' . $image->height] = $image->url;
                }
            }

            // ジャンル
            foreach ($row->genres as $genre) {
                $artistArray[$index]['genre'][] = $genre;
            }
        }
        return $artistArray;
    }

    /**
     * アーティストのジャンル情報をジャンルだけにまとめる
     * @param $artistArray
     * @return array
     */
    public function putArtistGenreArray($artistArray): array
    {
        $genreArray = [];
        foreach ($artistArray as $row) {
            if (!isset($row['genre'])) {
                continue;
            }

            foreach ($row['genre'] as $genre) {
                if (!array_search($genre, $genreArray)) {
                    $genreArray[] = $genre;
                }
            }
        }
        return $genreArray;
    }

    /**
     * @param array $artistArray
     * @return void
     */
    public function mapArtistGenre(array $artistArray): void
    {
        foreach ($artistArray as $row) {
            if (!isset($row['genre'])) {
                continue;
            }

            $artist = $row['artist'];
            $genreArray = $row['genre'];
            $artistId = $this->artistRepository->getArtistIdBySpotifyId($artist['spotify_id']);

            if (is_null($artistId)) {
                continue;
            }

            foreach ($genreArray as $genreName) {
                $genreId = $this->artistGenreRepository->getGenreIdByGenreName($genreName);
                if (is_null($genreId)) {
                    continue;
                }
                $this->artistGenreMapRepository->firstOrCreateArtistGenreMap($artistId, $genreId);
            }
        }
    }

    /**
     * Artist Top Rateの登録
     * @param array $artistArray
     * @return void
     */
    public function decideTopArtist(array $artistArray): void
    {
        $targetDate = date('Y-m-d');

        // 同じ日付のデータがあったら削除する
        if ($this->topArtistRepository->existTargetDateData($targetDate)) {
            $this->topArtistRepository->deleteTopArtistByTargetDate($targetDate);
        }

        foreach ($artistArray as $key => $row) {
            // Artist取得
            $artist = $this->artistRepository->getArtistIdBySpotifyId($row['artist']['spotify_id']);

            if (is_null($artist)) {
                continue;
            }

            $artistId = $artist->id;
            $popularity = $artist->popularity;
            $point = self::RANKING_POINT - (int)$key;

            $this->topArtistRepository->insertTopArtist($targetDate, $artistId, $popularity, $point);
        }
    }
}
