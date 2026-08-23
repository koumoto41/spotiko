<?php

namespace App\Domain\Spotify;

use Exception;

interface SpotifyClientInterface
{
    public function accessAuthorizeUrl(): string;

    public function getAccessToken(string $code, int $userId): void;

    public function refreshAccessToken(int $userId): void;

    public function checkRefreshToken(Exception $exception, int $userId): bool;

    public function getFeaturePlaylist(int $userId, int $limit = 20, int $offset = 0): object;

    public function getMyTop(
        int $userId,
        string $type,
        int $limit = 100,
        int $offset = 0,
        string $timeRange = 'short_term'
    ): object;

    public function getArtistById(int $userId, string $artistId): object;

    public function getAlbumById(int $userId, string $albumId): object;

    public function getNewReleases(int $userId, int $limit = 20, int $offset = 0): object;

    public function searchItem(
        int $userId,
        string $query,
        string $type,
        int $limit = 20,
        int $offset = 0
    ): object;
}
