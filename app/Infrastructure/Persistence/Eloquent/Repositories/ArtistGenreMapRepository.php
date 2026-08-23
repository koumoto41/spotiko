<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\ArtistGenreMap\Repositories\ArtistGenreMapRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ArtistGenreMap;

final class ArtistGenreMapRepository implements ArtistGenreMapRepositoryInterface
{
    public function __construct(private ArtistGenreMap $artistGenreMapModel) {}

    public function firstOrCreateArtistGenreMap(int $artistId, int $genreId): ArtistGenreMap
    {
        return $this->artistGenreMapModel->firstOrCreate([
            'artist_id' => $artistId,
            'genre_id' => $genreId,
        ]);
    }
}
