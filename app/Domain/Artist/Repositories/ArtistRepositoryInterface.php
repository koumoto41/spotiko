<?php

namespace App\Domain\Artist\Repositories;

interface ArtistRepositoryInterface
{
    public function existArtist(string $spotifyId): bool;

    public function getArtistIdBySpotifyId(string $spotifyId): ?int;

    /**
     * @param array<int, array{artist: array, genre?: array}> $artistArray
     */
    public function upsertArtist(array $artistArray): void;
}
