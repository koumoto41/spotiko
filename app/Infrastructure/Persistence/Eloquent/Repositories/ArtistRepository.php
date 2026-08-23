<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Artist\Repositories\ArtistRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Artist;

final class ArtistRepository implements ArtistRepositoryInterface
{
    public function __construct(private Artist $artistModel) {}

    public function existArtist(string $spotifyId): bool
    {
        return $this->artistModel->where('spotify_id', $spotifyId)->exists();
    }

    public function getArtistIdBySpotifyId(string $spotifyId): ?int
    {
        $data = $this->artistModel->where('spotify_id', $spotifyId)->first();

        return $data?->id;
    }

    public function upsertArtist(array $artistArray): void
    {
        $rows = array_column($artistArray, 'artist');

        if (! $rows) {
            return;
        }

        $this->artistModel->upsert(
            $rows,
            ['spotify_id'],
            ['name', 'url', 'popularity', 'img_640', 'img_320', 'img_160']
        );
    }
}
