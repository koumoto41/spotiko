<?php

namespace App\Domain\MyAlbum\Repositories;

interface MyAlbumRepositoryInterface
{
    public function existMyAlbum(int $albumId): bool;

    public function existMyAlbumBySpotifyId(string $spotifyId): bool;

    public function getMyAlbum(string $query, string $type, int $limit, int $offset): array;

    public function getMyAlbumCount(string $query, string $type): int;

    public function insertMyAlbum(array $myAlbum): void;
}
