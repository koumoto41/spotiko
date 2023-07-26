<?php

/**
 * Class StockService
 */

namespace App\Services\Cms;

use App\Services\SpotifyApiService;
use App\Services\TableService;
use App\Repositories\MyAlbumRepository;
use App\Repositories\AlbumRepositories;
use App\Repositories\ArtistRepositories;
use App\Repositories\ArtistGenreRepositories;
use App\Repositories\ArtistGenreMapRepositories;
use Illuminate\Http\Request;
use PHP_CodeSniffer\Tests\Core\File\testFECNNonExtendedClass;

class StockService
{
    private const TOP_TABLE_TH = ['画像', 'アルバム名', 'アーティスト名', '登録年月日'];
    private const SPOTIFT_ALBUM_TABLE_TH = ['画像', 'アルバム名', 'アーティスト名', 'リリース年', '追加'];

    public function __construct(
        private SpotifyApiService $spotifyApiService,
        private TableService $tableService,
        private MyAlbumRepository $myAlbumRepository,
        private AlbumRepositories $albumRepositories,
        private ArtistRepositories $artistRepositories,
        private ArtistGenreRepositories $artistGenreRepositories,
        private ArtistGenreMapRepositories $artistGenreMapRepositories
    ) {
    }

    /**
     * @param string $query
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getMyAlbumList(string $query, string $type, int $limit, int $offset): array
    {
        return [
            'count' => $this->myAlbumRepository->getMyAlbumCount($query, $type, 0, 0),
            'list' => $this->myAlbumRepository->getMyAlbum($query, $type, $limit, $offset),
            'paginate' => $this->myAlbumRepository->getMyAlbumPaginate($query, $type, $limit, $offset)
        ];
    }

    public function searchAlbumFromSpotify(string $query, int $limit, int $offset)
    {
        $albums = $this->spotify_api_library->search_item(1, $query, 'album', $limit, $offset);
        return $this->parse_spotify_album_data($albums);
    }

    private function parse_spotify_album_data($obj)
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
                'is_register' => $this->my_album_model->exist_my_album_by_spotify_id($item->id)
            ];
        }
        return $result;
    }

    /**
     * @param array $data
     * @param string $type
     * @return string
     */
    public function getTableView(array $data, string $type = 'top'): string
    {
        if (!$data) {
            return '';
        }

        if ($type === 'top') {
            $this->addTopTableTh();
            $this->addTopTableTd($data);
        } elseif ($type === 'spotify') {
            $this->addSpotifyTableTh();
            $this->addSpotifyTableTd($data);
        }

        return $this->tableService->getView();
    }

    /**
     ** @return void
     */
    public function addTopTableTh(): void
    {
        $this->tableService->setTh(self::TOP_TABLE_TH);
    }

    /**
     * @return void
     */
    public function addSpotifyTableTh(): void
    {
        $this->tableService->setTh(self::SPOTIFT_ALBUM_TABLE_TH);
    }

    /**
     * @param array $data
     * @return void
     */
    public function addTopTableTd(array $data): void
    {
        if (!$data) {
            return;
        }

        foreach ($data as $rowNumber => $item) {
            $this->tableService->setTd($rowNumber, [
                view('cms.parts.image')->with(['url' => $item['img_64']]),
                e($item['album_name']),
                e($item['artist_name']),
                e($item['created_at']),
            ]);
        }
    }

    /**
     * @param array $data
     * @return void
     */
    public function addSpotifyTableTd(array $data): void
    {
        if (!$data) {
            return;
        }

        foreach ($data as $rowNumber => $item) {
            $registerBadge = '';
            $deleteButton = view('cms.parts.button_js')->with([
                'text' => '追加',
                'type' => 'danger',
                'extra' => 'data-album_id="' . e($item['album_spotify_id']) . '" data-artist_id="' . e($item['artist_spotify_id']) . '"',
                'class' => 'btn-register'
            ]);

            if ($item['is_register']) {
                // データ登録済の場合は削除ボタン
                $registerBadge = view('cms.parts.badge')->with(['type' => 'danger', 'text' => '登録済']) . '<br>';
                $deleteButton = '';
            }

            $this->tableService->setTd($rowNumber, [
                view('cms.parts.image')->with(['url' => $item['image']]),
                $registerBadge . e($item['album_name']),
                e($item['artist_name']),
                e($item['release_date']),
                $deleteButton
            ]);
        }
    }

    /**
     * My Albumへの登録処理を行う
     * @param int $artistSpotifyId
     * @param int $albumSpotifyId
     * @return array
     */
    public function registerMyAlbum(int $artistSpotifyId, int $albumSpotifyId): array
    {
        if (!$this->artistRepositories->existArtist($artistSpotifyId)) {
            // アーティスト情報の登録が無ければ追加する
            $artist = $this->spotifyApiService->getArtistById(1, $artistSpotifyId);
            $artistArray = $this->makeArtistArray($artist);
            $genreArray = $this->putArtistGenreArray($artistArray);
            $this->artistRepositories->upsertArtist($artistArray);
            $this->artistGenreRepositories->upsertGenre($genreArray);
            $this->registerArtistGenreMap($artistArray);
        }
        $registerArtistId = $this->artistRepositories->getArtistIdBySpotifyId($artistSpotifyId);

        if (!$this->albumRepositories->existAlbum($albumSpotifyId)) {
            // アルバム情報の登録が無ければ追加する
            $album = $this->spotifyApiService->getAlbumById(1, $albumSpotifyId);
            $albumArray = $this->makeAlbumArray($album, $registerArtistId);
            $this->albumRepositories->insertAlbum($albumArray);
        }
        $registerAlbumId = $this->albumRepositories->getAlbumIdBySpotifyId($albumSpotifyId);

        if ($this->myAlbumRepository->existMyAlbum($registerAlbumId)) {
            return ['processing' => false, 'message' => 'すでに登録されています'];
        }

        $this->myAlbumRepository->insertMyAlbum([
            'album_id' => $registerAlbumId,
        ]);

        return ['processing' => true, 'message' => '登録が完了しました'];
    }

    /**
     * @param $artist
     * @return array
     */
    public function makeArtistArray($artist): array
    {
        $artistArray = [];

        if (isset($artist->items)) {
            $artistItems = $artist->items;
        } else {
            $artistItems[] = $artist;
        }

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
                # 画像の登録はこのタイプに限定
                if ($image->height === '640' || $image->height === '320' || $image->height === '160') {
                    $artistArray[$index]['artist']['img_' . $image->height] = $image->url;
                }
            }

            # ジャンル
            foreach ($row->genres as $genre) {
                $artistArray[$index]['genre'][] = $genre;
            }
        }
        return $artistArray;
    }

    /**
     * @param array $artistArray
     * @return array
     */
    public function putArtistGenreArray(array $artistArray): array
    {
        $result = [];

        foreach ($artistArray as $row) {
            if (!isset($row['genre'])) {
                continue;
            }

            foreach ($row['genre'] as $genre) {
                if (!array_search($genre, $result)) {
                    $result[] = $genre;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $artistArray
     * @return void
     */
    public function registerArtistGenreMap(array $artistArray): void
    {
        foreach ($artistArray as $row) {
            if (!isset($row['genre'])) {
                continue;
            }

            $artist = $row['artist'];
            $genreArray = $row['genre'];

            $artist_id = $this->artistRepositories->getArtistIdBySpotifyId($artist['spotify_id']);
            if (!$artist_id) {
                continue;
            }

            foreach ($genreArray as $genre) {
                $this->artistGenreMapRepositories->firstOrCreateArtistGenreMap(
                    $artist_id,
                    $this->artistGenreRepositories->getGenreIdByGenreName($genre)
                );
            }
        }
    }

    /**
     * @param $album
     * @param int $artistId
     * @return array
     */
    public function makeAlbumArray($album, int $artistId): array
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
            // 画像の登録はこのタイプに限定
            if ($image->height === '640' || $image->height === '300' || $image->height === '64') {
                $albumArray['img_' . $image->height] = $image->url;
            }
        }

        return $albumArray;
    }
}
