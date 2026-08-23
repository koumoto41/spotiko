<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\TopTrack\Repositories\TopTrackRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\TopTrack;
use Illuminate\Support\Collection;

final class TopTrackRepository implements TopTrackRepositoryInterface
{
    public function __construct(private TopTrack $topTrackModel)
    {
    }

    public function getLatestAggregateDate(): ?string
    {
        return $this->topTrackModel
            ->groupBy('target_date')
            ->latest('target_date')
            ->value('target_date');
    }

    public function getTopTrackByAggregateDate(string $aggregateDate, bool $isRand = false): Collection
    {
        $query = $this->topTrackModel
            ->join('track', 'top_track.track_id', '=', 'track.id')
            ->join('artist', 'top_track.artist_id', '=', 'artist.id')
            ->join('album', 'top_track.album_id', '=', 'album.id')
            ->where('target_date', $aggregateDate);

        $query = $isRand ? $query->inRandomOrder() : $query->latest('target_date');

        return $query->select(
            'album.name AS album_name',
            'track.name AS track_name',
            'artist.name AS artist_name',
            'artist.url AS artist_url',
            'track.url AS track_url'
        )->get();
    }

    public function existTargetDateData(string $targetDate): bool
    {
        return $this->topTrackModel->where('target_date', $targetDate)->exists();
    }

    public function deleteTopTrackByTargetDate(string $targetDate): void
    {
        $this->topTrackModel->where('target_date', $targetDate)->delete();
    }

    public function insertTopTrack(
        string $targetDate,
        int $artistId,
        int $albumId,
        int $trackId,
        int $popularity,
        int $point
    ): void {
        $this->topTrackModel->newInstance()->fill([
            'target_date' => $targetDate,
            'artist_id' => $artistId,
            'album_id' => $albumId,
            'track_id' => $trackId,
            'popularity' => $popularity,
            'point' => $point,
        ])->save();
    }
}
