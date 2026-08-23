<?php

namespace App\Repositories;

use App\Models\Artist_Genre_Map;

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
     * @return Artist_Genre_Map
     */
    public function firstOrCreateArtistGenreMap(int $artistId, int $genreId): Artist_Genre_Map
    {
        return $this->artistGenreMapModel->firstOrCreate([
            'artist_id' => $artistId,
            'genre_id' => $genreId
        ]);
    }
}
