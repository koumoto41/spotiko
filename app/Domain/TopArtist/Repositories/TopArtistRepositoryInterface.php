<?php

namespace App\Domain\TopArtist\Repositories;

use Illuminate\Support\Collection;

interface TopArtistRepositoryInterface
{
    public function getLatestAggregateDate(): ?string;

    public function getTopArtistByAggregateDate(string $aggregateDate, bool $isRand = false): Collection;

    public function existTargetDateData(string $targetDate): bool;

    public function deleteTopArtistByTargetDate(string $targetDate): void;

    public function insertTopArtist(string $targetDate, int $artistId, int $popularity, int $point): void;
}
