<?php

namespace App\Domain\ArtistGenre\Repositories;

interface ArtistGenreRepositoryInterface
{
    public function getGenreIdByGenreName(string $genreName): ?int;

    /**
     * @param  array<int, string>  $genreArray
     */
    public function upsertGenre(array $genreArray): void;
}
