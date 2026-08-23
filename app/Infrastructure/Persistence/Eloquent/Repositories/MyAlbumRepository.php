<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\MyAlbum\Repositories\MyAlbumRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\MyAlbum;
use Illuminate\Database\Eloquent\Builder;

final class MyAlbumRepository implements MyAlbumRepositoryInterface
{
    public function __construct(private MyAlbum $myAlbumModel)
    {
    }

    public function existMyAlbum(int $albumId): bool
    {
        return $this->myAlbumModel->where('album_id', $albumId)->exists();
    }

    public function existMyAlbumBySpotifyId(string $spotifyId): bool
    {
        return $this->myAlbumModel
            ->join('album', 'my_album.album_id', '=', 'album.id')
            ->where('album.spotify_id', $spotifyId)
            ->exists();
    }

    public function getMyAlbum(string $query, string $type, int $limit, int $offset): array
    {
        return $this->baseQuery($query, $type)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getMyAlbumCount(string $query, string $type): int
    {
        return $this->baseQuery($query, $type)->count();
    }

    public function insertMyAlbum(array $myAlbum): void
    {
        $this->myAlbumModel->newInstance()->fill($myAlbum)->save();
    }

    private function baseQuery(string $query, string $type): Builder
    {
        $columns = [
            'album.name AS album_name',
            'artist.name AS artist_name',
            'album.img_640 AS img_640',
            'album.img_300 AS img_300',
            'album.img_64 AS img_64',
            'album.url AS url',
            'my_album.created_at AS created_at',
        ];

        $builder = $this->myAlbumModel
            ->select($columns)
            ->join('album', 'my_album.album_id', '=', 'album.id')
            ->join('artist', 'album.artist_id', '=', 'artist.id')
            ->orderByRaw('artist.name, album.name');

        if ($type === 'album') {
            $builder = $builder->where('album.name', 'like', "%{$query}%");
        } elseif ($type === 'artist') {
            $builder = $builder->where('artist.name', 'like', "%{$query}%");
        }

        return $builder;
    }
}
