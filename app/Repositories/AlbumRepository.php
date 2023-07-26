<?php

namespace App\Repositories;

use App\Models\Album;
use Illuminate\Support\Collection;

final class AlbumRepository
{
    public function __construct(private Album $albumModel)
    {
    }

    /**
     * Spotify IDからAlbumが登録されているか調べる
     * @param int $spotifyId
     * @return bool
     */
    public function existAlbum(int $spotifyId): bool
    {
        return $this->albumModel->where('spotify_id', $spotifyId)->exists();
    }

    /**
     * Spotify IDからAlbum IDを取得する
     * @param int $spotifyId
     * @return int|null
     */
    public function getAlbumIdBySpotifyId(int $spotifyId): int|null
    {
        $data = $this->albumModel->where('spotify_id', $spotifyId)->first();
        if ($data) {
            return $data->id;
        }
        return null;
    }

    /**
     * Album追加
     * @param $album
     * @return void
     */
    public function insertAlbum($album): void
    {
        $this->albumModel->fill($album)->save();
    }

    /**
     * アルバム一覧を取得
     * @param int $limit
     * @param int $offset
     * @return Collection
     */
    public function getAlbumList(int $limit, int $offset): Collection
    {
        $gettingColumns = [
            'album.id AS album_id',
            'album.name AS album_name',
            'artist.name AS artist_name',
        ];

        return $this->albumModel
            ->select($gettingColumns)
            ->Join('artist', 'album.artist_id', '=', 'artist.id')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }
}
