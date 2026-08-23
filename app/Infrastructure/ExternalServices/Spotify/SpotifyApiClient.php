<?php

namespace App\Infrastructure\ExternalServices\Spotify;

use App\Domain\Spotify\SpotifyClientInterface;
use App\Domain\Token\Repositories\TokenRepositoryInterface;
use Exception;
use SpotifyWebAPI;

final class SpotifyApiClient implements SpotifyClientInterface
{
    private const SEARCH_LIMIT = 20;

    private SpotifyWebAPI\Session $session;

    public function __construct(
        private TokenRepositoryInterface $tokenRepository,
        private SpotifyWebAPI\SpotifyWebAPI $spotifyWebApi
    ) {
        $this->session = new SpotifyWebAPI\Session(
            config('spotify.CLIENT_ID'),
            config('spotify.CLIENT_SECRET'),
            config('spotify.REDIRECT_URI')
        );
    }

    private function saveAccessToken(int $userId): void
    {
        if ($this->tokenRepository->getTokenByUserId($userId)) {
            $this->tokenRepository->deleteToken($userId);
        }

        $this->tokenRepository->insertToken(
            $userId,
            $this->session->getAccessToken(),
            $this->session->getRefreshToken()
        );
    }

    public function accessAuthorizeUrl(): string
    {
        return $this->session->getAuthorizeUrl(['scope' => config('spotify.scope')]);
    }

    public function getAccessToken(string $code, int $userId): void
    {
        $this->session->requestAccessToken($code);
        $this->saveAccessToken($userId);
    }

    public function refreshAccessToken(int $userId): void
    {
        $tokenData = $this->tokenRepository->getTokenByUserId($userId);
        $this->session->refreshAccessToken($tokenData?->refresh_token);
        $this->saveAccessToken($userId);
    }

    public function checkRefreshToken(Exception $exception, int $userId): bool
    {
        if ($exception->getCode() === 401 && $exception->getMessage() === 'The access token expired') {
            $this->refreshAccessToken($userId);

            return true;
        }

        return false;
    }

    private function setAccessToken(int $userId): void
    {
        $this->refreshAccessToken($userId);

        $tokenData = $this->tokenRepository->getTokenByUserId($userId);
        $this->spotifyWebApi->setAccessToken($tokenData?->token);
    }

    public function getFeaturePlaylist(int $userId, int $limit = 20, int $offset = 0): object
    {
        $this->setAccessToken($userId);

        return $this->spotifyWebApi->getFeaturedPlaylists([
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getMyTop(
        int $userId,
        string $type,
        int $limit = 100,
        int $offset = 0,
        string $timeRange = 'short_term'
    ): object {
        $this->setAccessToken($userId);

        return $this->spotifyWebApi->getMyTop($type, [
            'limit' => $limit,
            'offset' => $offset,
            'time_range' => $timeRange,
        ]);
    }

    public function getArtistById(int $userId, string $artistId): object
    {
        $this->setAccessToken($userId);

        return $this->spotifyWebApi->getArtist($artistId);
    }

    public function getAlbumById(int $userId, string $albumId): object
    {
        $this->setAccessToken($userId);

        return $this->spotifyWebApi->getAlbum($albumId);
    }

    public function getNewReleases(int $userId, int $limit = 20, int $offset = 0): object
    {
        $this->setAccessToken($userId);

        return $this->spotifyWebApi->getNewReleases([
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function searchItem(
        int $userId,
        string $query,
        string $type,
        int $limit = self::SEARCH_LIMIT,
        int $offset = 0
    ): object {
        $this->setAccessToken($userId);

        return $this->spotifyWebApi->search($query, $type, [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
}
