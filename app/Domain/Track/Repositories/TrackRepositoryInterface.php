<?php

namespace App\Domain\Track\Repositories;

interface TrackRepositoryInterface
{
    public function existTrack(string $spotifyId): bool;

    public function getTrackIdBySpotifyId(string $spotifyId): ?int;

    /**
     * @param  array<int, array{track: array}>  $trackArray
     */
    public function upsertTrack(array $trackArray): void;
}
