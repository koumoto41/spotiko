<?php

namespace App\Domain\Album\Repositories;

use Illuminate\Support\Collection;

interface AlbumRepositoryInterface
{
    public function existAlbum(string $spotifyId): bool;

    public function getAlbumIdBySpotifyId(string $spotifyId): ?int;

    public function insertAlbum(array $album): void;

    public function getAlbumList(int $limit, int $offset): Collection;
}
