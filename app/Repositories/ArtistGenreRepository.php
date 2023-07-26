<?php

namespace App\Repositories;

use App\Models\Artist_Genre;
use Illuminate\Support\Collection;

final class ArtistGenreRepository
{
    public function __construct(private Artist_Genre $artistGenreModel)
    {
    }

    /**
     * ジャンル名からジャンルIDを取得する
     * @param $genre_name
     * @return int|null
     */
    public function getGenreIdByGenreName($genre_name): ?int
    {
        $data = $this->artistGenreModel->where('name', $genre_name)->first();

        if ($data) {
            return $data->id;
        }
        return null;
    }

    /**
     * データがなかったら登録する
     * @param $genreArray
     * @return void
     */
    public function upsertGenre($genreArray): void
    {
        $this->artistGenreModel->upsert($genreArray, ['name'], ['name']);
    }
}
