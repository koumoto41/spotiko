<?php

namespace App\Domain\ArtistGenreMap\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\ArtistGenreMap;

interface ArtistGenreMapRepositoryInterface
{
    public function firstOrCreateArtistGenreMap(int $artistId, int $genreId): ArtistGenreMap;
}
