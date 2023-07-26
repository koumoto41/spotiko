<?php

namespace App\Repositories;

use App\Models\Artist_Genre_Map;
use Illuminate\Support\Collection;

final class ArtistGenreMapRepository
{
    public function __construct(
        private Artist_Genre_Map $artistGenreMapModel
    ) {
    }

    /**
     * データが無かったら追加する
     * @param int $artistId
     * @param int $genreId
     * @return Collection
     */
    public function firstOrCreateArtistGenreMap(int $artistId, int $genreId): Collection
    {
        return $this->artistGenreMapModel->firstOrCreate([
            'artist_id' => $artistId,
            'genre_id' => $genreId
        ]);
    }
}
