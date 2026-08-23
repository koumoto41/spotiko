<?php

namespace App\Application\Services\Cms;

use App\Domain\Album\Repositories\AlbumRepositoryInterface;
use App\Domain\Artist\Repositories\ArtistRepositoryInterface;
use App\Domain\ArtistGenre\Repositories\ArtistGenreRepositoryInterface;
use App\Domain\ArtistGenreMap\Repositories\ArtistGenreMapRepositoryInterface;
use App\Domain\MyAlbum\Repositories\MyAlbumRepositoryInterface;
use App\Domain\Spotify\SpotifyClientInterface;

final class StockService
{
    private const REGISTER_USER_ID = 1;

    public function __construct(
        private SpotifyClientInterface $spotifyClient,
        private MyAlbumRepositoryInterface $myAlbumRepository,
        private AlbumRepositoryInterface $albumRepository,
        private ArtistRepositoryInterface $artistRepository,
        private ArtistGenreRepositoryInterface $artistGenreRepository,
        private ArtistGenreMapRepositoryInterface $artistGenreMapRepository
    ) {}

    public function getMyAlbumList(string $query, string $type, int $limit, int $offset): array
    {
        return [
            'count' => $this->myAlbumRepository->getMyAlbumCount($query, $type),
            'list' => $this->myAlbumRepository->getMyAlbum($query, $type, $limit, $offset),
        ];
    }

    public function searchAlbumFromSpotify(string $query, int $limit, int $offset): array
    {
        $albums = $this->spotifyClient->searchItem(self::REGISTER_USER_ID, $query, 'album', $limit, $offset);

        return $this->parseSpotifyAlbumData($albums);
    }

    private function parseSpotifyAlbumData(object $obj): array
    {
        $result = [];
        foreach ($obj->albums->items as $item) {
            $result[] = [
                'album_name' => $item->name,
                'album_spotify_id' => $item->id,
                'release_date' => $item->release_date,
                'type' => $item->type,
                'artist_name' => $item->artists[0]->name,
                'artist_spotify_id' => $item->artists[0]->id,
                'image' => $item->images[0]->url ?? '',
                'is_register' => $this->myAlbumRepository->existMyAlbumBySpotifyId($item->id),
            ];
        }

        return $result;
    }

    /**
     * My Albumへの登録処理を行う
     */
    public function registerMyAlbum(string $artistSpotifyId, string $albumSpotifyId): array
    {
        if (! $this->artistRepository->existArtist($artistSpotifyId)) {
            $artist = $this->spotifyClient->getArtistById(self::REGISTER_USER_ID, $artistSpotifyId);
            $artistArray = $this->makeArtistArray($artist);
            $genreArray = $this->putArtistGenreArray($artistArray);

            $this->artistRepository->upsertArtist($artistArray);
            $this->artistGenreRepository->upsertGenre($genreArray);
            $this->registerArtistGenreMap($artistArray);
        }
        $registerArtistId = $this->artistRepository->getArtistIdBySpotifyId($artistSpotifyId);

        if (! $this->albumRepository->existAlbum($albumSpotifyId)) {
            $album = $this->spotifyClient->getAlbumById(self::REGISTER_USER_ID, $albumSpotifyId);
            $albumArray = $this->makeAlbumArray($album, $registerArtistId);
            $this->albumRepository->insertAlbum($albumArray);
        }
        $registerAlbumId = $this->albumRepository->getAlbumIdBySpotifyId($albumSpotifyId);

        if ($this->myAlbumRepository->existMyAlbum($registerAlbumId)) {
            return ['processing' => false, 'message' => 'すでに登録されています'];
        }

        $this->myAlbumRepository->insertMyAlbum(['album_id' => $registerAlbumId]);

        return ['processing' => true, 'message' => '登録が完了しました'];
    }

    private function makeArtistArray(object $artist): array
    {
        $artistItems = isset($artist->items) ? $artist->items : [$artist];

        $artistArray = [];
        foreach ($artistItems as $index => $row) {
            $artistArray[$index]['artist']['spotify_id'] = $row->id;
            $artistArray[$index]['artist']['name'] = $row->name;
            $artistArray[$index]['artist']['url'] = $row->external_urls->spotify;
            $artistArray[$index]['artist']['popularity'] = $row->popularity;

            $artistArray[$index]['artist']['img_640'] = '';
            $artistArray[$index]['artist']['img_320'] = '';
            $artistArray[$index]['artist']['img_160'] = '';

            foreach ($row->images as $image) {
                if (in_array((string) $image->height, ['640', '320', '160'], true)) {
                    $artistArray[$index]['artist']['img_'.$image->height] = $image->url;
                }
            }

            foreach ($row->genres as $genre) {
                $artistArray[$index]['genre'][] = $genre;
            }
        }

        return $artistArray;
    }

    private function putArtistGenreArray(array $artistArray): array
    {
        $result = [];
        foreach ($artistArray as $row) {
            if (! isset($row['genre'])) {
                continue;
            }

            foreach ($row['genre'] as $genre) {
                if (! in_array($genre, $result, true)) {
                    $result[] = $genre;
                }
            }
        }

        return $result;
    }

    private function registerArtistGenreMap(array $artistArray): void
    {
        foreach ($artistArray as $row) {
            if (! isset($row['genre'])) {
                continue;
            }

            $artist = $row['artist'];
            $artistId = $this->artistRepository->getArtistIdBySpotifyId($artist['spotify_id']);
            if (! $artistId) {
                continue;
            }

            foreach ($row['genre'] as $genre) {
                $genreId = $this->artistGenreRepository->getGenreIdByGenreName($genre);
                if (is_null($genreId)) {
                    continue;
                }
                $this->artistGenreMapRepository->firstOrCreateArtistGenreMap($artistId, $genreId);
            }
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
                $albumArray['img_'.$image->height] = $image->url;
            }
        }

        return $albumArray;
    }
}
