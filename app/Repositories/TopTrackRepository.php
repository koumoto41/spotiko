<?php

namespace App\Repositories;

use App\Models\Top_Track;
use Illuminate\Support\Collection;

final class TopTrackRepository
{
    public function __construct(private Top_Track $topTrackModel)
    {
    }

    /**
     * 集計を取ってる日付を取得
     * @param bool $isFirst
     * @return string
     */
    public function getAggregateDate(bool $isFirst = false): string
    {
        $aggregateDate = $this->topTrackModel
            ->groupBy('target_date')
            ->latest('target_date')
            ->pluck('target_date');

        return $isFirst ? $aggregateDate->first() : $aggregateDate;
    }

    /**
     * 集計日から曲目を取得
     * @param string $aggregateDate
     * @param bool $isRand
     * @return Collection
     */
    public function getTopTrackByAggregateDate(string $aggregateDate, bool $isRand = false): Collection
    {
        $topTrack = $this->topTrackModel
            ->join('track', 'top_track.track_id', '=', 'track.id')
            ->join('artist', 'top_track.artist_id', '=', 'artist.id')
            ->join('album', 'top_track.album_id', '=', 'album.id')
            ->where('target_date', $aggregateDate);

        $topTrack = $isRand ? $topTrack->inRandomOrder() : $topTrack->latest('target_date');
        return $topTrack->select('album.name AS album_name', 'track.name AS track_name', 'artist.name AS artist_name', 'artist.url AS artist_url', 'track.url AS track_url')->get();
    }
}
