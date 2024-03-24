<?php

namespace App\Repositories;

use App\Models\My_Album;
use Illuminate\Support\Collection;

final class MyAlbumRepository
{
    public function __construct(private My_Album $myAlbumModel)
    {
    }

    /**
     * @param int $albumId
     * @return bool
     */
    public function existMyAlbum(int $albumId): bool
    {
        return $this->myAlbumModel->where('album_id', $albumId)->exists();
    }

    /**
     * @param int $spotifyId
     * @return bool
     */
    public function existMyAlbumBySpotifyId(int $spotifyId): bool
    {
        return $this->myAlbumModel
            ->join('album', 'my_album.album_id', '=', 'album.id')
            ->where('album.spotify_id', $spotifyId)
            ->exists();
    }

    /**
     * @param string $query
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getMyAlbum(string $query, string $type, int $limit, int $offset): array
    {
        return $this->makeMyAlbumObject($query, $type, $limit, $offset)->get()->toArray();
    }

    /**
     * @param string $query
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return int
     */
    public function getMyAlbumCount(string $query, string $type, int $limit, int $offset): int
    {
        return $this->makeMyAlbumObject($query, $type, $limit, $offset)->count();
    }

    /**
     * @param string $query
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getMyAlbumPaginate(string $query, string $type, int $limit, int $offset): string
    {
        return $this->makeMyAlbumObject($query, $type, $limit, $offset)->paginate($limit);
    }

    /**
     * @param string $query
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function makeMyAlbumObject(string $query, string $type, int $limit, int $offset)
    {
        $gettingColumns = [
            'album.name AS album_name',
            'artist.name AS artist_name',
            'album.img_640 AS img_640',
            'album.img_300 AS img_300',
            'album.img_64 AS img_64',
            'album.url AS url',
            'my_album.created_at AS created_at'
        ];

        $myAlbum = $this->myAlbumModel
            ->select($gettingColumns)
            ->Join('album', 'my_album.album_id', '=', 'album.id')
            ->Join('artist', 'album.artist_id', '=', 'artist.id')
            ->orderByRaw('artist.name, album.name');

        $myAlbum = $limit ? $myAlbum->limit($limit) : $myAlbum;
        $myAlbum = $offset ? $myAlbum->offset($offset) : $myAlbum;

        if ($type === 'album') {
            $myAlbum = $myAlbum->where('album.name', 'like', "%{$query}%");
        } elseif ($type === 'artist') {
            $myAlbum = $myAlbum->where('artist.name', 'like', "%{$query}%");
        }

        return $myAlbum;
    }

    /**
     * @param $myAlbum
     * @return void
     */
    public function insertMyAlbum($myAlbum): void
    {
        $this->myAlbumModel->fill($myAlbum)->save();
    }
}
