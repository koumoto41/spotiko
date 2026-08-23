<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Album\Repositories\AlbumRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Album;
use Illuminate\Support\Collection;

final class AlbumRepository implements AlbumRepositoryInterface
{
    public function __construct(private Album $albumModel)
    {
    }

    public function existAlbum(string $spotifyId): bool
    {
        return $this->albumModel->where('spotify_id', $spotifyId)->exists();
    }

    public function getAlbumIdBySpotifyId(string $spotifyId): ?int
    {
        $data = $this->albumModel->where('spotify_id', $spotifyId)->first();

        return $data?->id;
    }

    public function insertAlbum(array $album): void
    {
        $this->albumModel->newInstance()->fill($album)->save();
    }

    public function getAlbumList(int $limit, int $offset): Collection
    {
        return $this->albumModel
            ->select(['album.id AS album_id', 'album.name AS album_name', 'artist.name AS artist_name'])
            ->join('artist', 'album.artist_id', '=', 'artist.id')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }
}
