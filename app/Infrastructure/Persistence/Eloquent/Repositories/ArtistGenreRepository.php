<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\ArtistGenre\Repositories\ArtistGenreRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ArtistGenre;

final class ArtistGenreRepository implements ArtistGenreRepositoryInterface
{
    public function __construct(private ArtistGenre $artistGenreModel)
    {
    }

    public function getGenreIdByGenreName(string $genreName): ?int
    {
        $data = $this->artistGenreModel->where('name', $genreName)->first();

        return $data?->id;
    }

    public function upsertGenre(array $genreArray): void
    {
        $rows = array_map(static fn (string $name): array => ['name' => $name], $genreArray);

        if (!$rows) {
            return;
        }

        $this->artistGenreModel->upsert($rows, ['name'], ['name']);
    }
}
