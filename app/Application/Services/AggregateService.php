<?php

namespace App\Application\Services;

use App\Domain\Album\Repositories\AlbumRepositoryInterface;
use App\Domain\Artist\Repositories\ArtistRepositoryInterface;
use App\Domain\ArtistGenre\Repositories\ArtistGenreRepositoryInterface;
use App\Domain\ArtistGenreMap\Repositories\ArtistGenreMapRepositoryInterface;
use App\Domain\Spotify\SpotifyClientInterface;
use App\Domain\Track\Repositories\TrackRepositoryInterface;
use App\Domain\TopArtist\Repositories\TopArtistRepositoryInterface;
use App\Domain\TopTrack\Repositories\TopTrackRepositoryInterface;

final class AggregateService
{
    // 登録許可画像サイズ
    public const ALLOWED_IMAGE_WIDTH = ['640', '320', '160'];
    public const RANKING_POINT = 50;

    public function __construct(
        private SpotifyClientInterface $spotifyClient,
        private ArtistRepositoryInterface $artistRepository,
        private AlbumRepositoryInterface $albumRepository,
        private TrackRepositoryInterface $trackRepository,
        private ArtistGenreRepositoryInterface $artistGenreRepository,
        private ArtistGenreMapRepositoryInterface $artistGenreMapRepository,
        private TopArtistRepositoryInterface $topArtistRepository,
        private TopTrackRepositoryInterface $topTrackRepository
    ) {
    }

    /**
     * SpotifyAPI:getMyTopで取得したartist情報を配列に変換
     */
    public function convertMyTopArtistToArrayBySpotifyApi(object $myTopArtist): array
    {
        $artistItems = isset($myTopArtist->items) ? $myTopArtist->items : [$myTopArtist];

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
                if (in_array((string) $image->height, self::ALLOWED_IMAGE_WIDTH, true)) {
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
     */
    public function putArtistGenreArray(array $artistArray): array
    {
        $genreArray = [];
        foreach ($artistArray as $row) {
            if (!isset($row['genre'])) {
                continue;
            }

            foreach ($row['genre'] as $genre) {
                if (!in_array($genre, $genreArray, true)) {
                    $genreArray[] = $genre;
                }
            }
        }

        return $genreArray;
    }

    public function mapArtistGenre(array $artistArray): void
    {
        foreach ($artistArray as $row) {
            if (!isset($row['genre'])) {
                continue;
            }

            $artist = $row['artist'];
            $artistId = $this->artistRepository->getArtistIdBySpotifyId($artist['spotify_id']);

            if (is_null($artistId)) {
                continue;
            }

            foreach ($row['genre'] as $genreName) {
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
     */
    public function decideTopArtist(array $artistArray): void
    {
        $targetDate = date('Y-m-d');

        // 同じ日付のデータがあったら削除する
        if ($this->topArtistRepository->existTargetDateData($targetDate)) {
            $this->topArtistRepository->deleteTopArtistByTargetDate($targetDate);
        }

        foreach ($artistArray as $key => $row) {
            $artistId = $this->artistRepository->getArtistIdBySpotifyId($row['artist']['spotify_id']);

            if (is_null($artistId)) {
                continue;
            }

            $popularity = $row['artist']['popularity'];
            $point = self::RANKING_POINT - (int) $key;

            $this->topArtistRepository->insertTopArtist($targetDate, $artistId, $popularity, $point);
        }
    }

    /**
     * SpotifyAPI:getMyTopで取得したtrack情報を配列に変換
     * アーティスト、アルバムの情報が無ければAPI経由で登録も行う
     */
    public function convertMyTopTrackToArrayBySpotifyApi(object $track, int $userId): array
    {
        $trackArray = [];

        foreach ($track->items as $index => $row) {
            $artistSpotifyId = $row->artists[0]->id;
            $albumSpotifyId = $row->album->id;

            $trackArray[$index]['track']['spotify_id'] = $row->id;
            $trackArray[$index]['track']['name'] = $row->name;
            $trackArray[$index]['track']['url'] = $row->external_urls->spotify;
            $trackArray[$index]['track']['popularity'] = $row->popularity;
            $trackArray[$index]['track']['track_number'] = $row->track_number;
            $trackArray[$index]['track']['preview_url'] = $row->preview_url;

            // アーティストの情報が無い場合は登録を行う
            if (!$this->artistRepository->existArtist($artistSpotifyId)) {
                $artist = $this->spotifyClient->getArtistById($userId, $artistSpotifyId);
                $artistArray = $this->convertMyTopArtistToArrayBySpotifyApi($artist);
                $genreArray = $this->putArtistGenreArray($artistArray);

                $this->artistRepository->upsertArtist($artistArray);
                $this->artistGenreRepository->upsertGenre($genreArray);
                $this->mapArtistGenre($artistArray);
            }

            $trackArray[$index]['track']['artist_id'] = $this->artistRepository->getArtistIdBySpotifyId($artistSpotifyId);

            // アルバムの情報が無い場合は登録を行う
            if (!$this->albumRepository->existAlbum($albumSpotifyId)) {
                $album = $this->spotifyClient->getAlbumById($userId, $albumSpotifyId);
                $albumArray = $this->makeAlbumArray($album, $trackArray[$index]['track']['artist_id']);
                $this->albumRepository->insertAlbum($albumArray);
            }

            $trackArray[$index]['track']['album_id'] = $this->albumRepository->getAlbumIdBySpotifyId($albumSpotifyId);
        }

        return $trackArray;
    }

    /**
     * Track Top Rateの登録
     */
    public function decideTopTrack(array $trackArray): void
    {
        $targetDate = date('Y-m-d');

        if ($this->topTrackRepository->existTargetDateData($targetDate)) {
            $this->topTrackRepository->deleteTopTrackByTargetDate($targetDate);
        }

        foreach ($trackArray as $key => $row) {
            $trackId = $this->trackRepository->getTrackIdBySpotifyId($row['track']['spotify_id']);

            if (is_null($trackId)) {
                continue;
            }

            $popularity = $row['track']['popularity'];
            $artistId = $row['track']['artist_id'];
            $albumId = $row['track']['album_id'];
            $point = self::RANKING_POINT - (int) $key;

            $this->topTrackRepository->insertTopTrack($targetDate, $artistId, $albumId, $trackId, $popularity, $point);
        }
    }

    private function makeAlbumArray(object $album, int $artistId): array
    {
        $albumArray = [
            'spotify_id' => $album->id,
            'artist_id' => $artistId,
            'name' => $album->name,
            'url' => $album->external_urls->spotify,
            'release_date' => $album->release_date,
            'total_track' => $album->total_tracks,
            'type' => $album->album_type,
            'img_640' => '',
            'img_300' => '',
            'img_64' => '',
        ];

        foreach ($album->images as $image) {
            if (in_array((string) $image->height, ['640', '300', '64'], true)) {
                $albumArray['img_' . $image->height] = $image->url;
            }
        }

        return $albumArray;
    }
}
