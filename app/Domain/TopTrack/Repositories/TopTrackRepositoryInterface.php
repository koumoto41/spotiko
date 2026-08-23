<?php

namespace App\Domain\TopTrack\Repositories;

use Illuminate\Support\Collection;

interface TopTrackRepositoryInterface
{
    public function getLatestAggregateDate(): ?string;

    public function getTopTrackByAggregateDate(string $aggregateDate, bool $isRand = false): Collection;

    public function existTargetDateData(string $targetDate): bool;

    public function deleteTopTrackByTargetDate(string $targetDate): void;

    public function insertTopTrack(
        string $targetDate,
        int $artistId,
        int $albumId,
        int $trackId,
        int $popularity,
        int $point
    ): void;
}
