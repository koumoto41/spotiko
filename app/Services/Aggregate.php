<?php

namespace App\Services;

use App\Repositories\ArtistGenreMapRepositories;
use App\Repositories\ArtistGenreRepositories;
use App\Repositories\ArtistRepositories;
use App\Repositories\TopArtistRepositories;

final class Aggregate
{
    // 登録許可画像サイズ
    public const ALLOWED_IMAGE_WIDTH = ['640', '320', '160'];
    public const RANKING_POINT = 50;

    public function __construct(
        private SpotifyApi $spotifyApi,
        private ArtistRepositories $artistRepositories,
        private ArtistGenreRepositories $artistGenreRepositories,
        private ArtistGenreMapRepositories $artistGenreMapRepositories,
        private TopArtistRepositories $topArtistRepositories
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
            $artistId = $this->artistRepositories->getArtistIdBySpotifyId($artist['spotify_id']);

            if (is_null($artistId)) {
                continue;
            }

            foreach ($genreArray as $genreName) {
                $genreId = $this->artistGenreRepositories->getGenreIdByGenreName($genreName);
                if (is_null($genreId)) {
                    continue;
                }
                $this->artistGenreMapRepositories->firstOrCreateArtistGenreMap($artistId, $genreId);
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
        if ($this->topArtistRepositories->existTargetDateData($targetDate)) {
            $this->topArtistRepositories->deleteTopArtistByTargetDate($targetDate);
        }

        foreach ($artistArray as $key => $row) {
            // Artist取得
            $artist = $this->artistRepositories->getArtistIdBySpotifyId($row['artist']['spotify_id']);

            if (is_null($artist)) {
                continue;
            }

            $artistId = $artist->id;
            $popularity = $artist->popularity;
            $point = self::RANKING_POINT - (int)$key;

            $this->topArtistRepositories->insertTopArtist($targetDate, $artistId, $popularity, $point);
        }
    }
}
