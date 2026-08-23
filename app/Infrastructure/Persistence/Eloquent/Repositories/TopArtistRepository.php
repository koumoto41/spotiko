<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\TopArtist\Repositories\TopArtistRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\TopArtist;
use Illuminate\Support\Collection;

final class TopArtistRepository implements TopArtistRepositoryInterface
{
    public function __construct(private TopArtist $topArtistModel)
    {
    }

    public function getLatestAggregateDate(): ?string
    {
        return $this->topArtistModel
            ->groupBy('target_date')
            ->latest('target_date')
            ->value('target_date');
    }

    public function getTopArtistByAggregateDate(string $aggregateDate, bool $isRand = false): Collection
    {
        $query = $this->topArtistModel
            ->join('artist', 'top_artist.artist_id', '=', 'artist.id')
            ->where('target_date', $aggregateDate);

        $query = $isRand ? $query->inRandomOrder() : $query->latest('target_date');

        return $query->get();
    }

    public function existTargetDateData(string $targetDate): bool
    {
        return $this->topArtistModel->where('target_date', $targetDate)->exists();
    }

    public function deleteTopArtistByTargetDate(string $targetDate): void
    {
        $this->topArtistModel->where('target_date', $targetDate)->delete();
    }

    public function insertTopArtist(string $targetDate, int $artistId, int $popularity, int $point): void
    {
        $this->topArtistModel->newInstance()->fill([
            'target_date' => $targetDate,
            'artist_id' => $artistId,
            'popularity' => $popularity,
            'point' => $point,
        ])->save();
    }
}
