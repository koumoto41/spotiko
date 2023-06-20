<?php

namespace App\Repositories;

use App\Models\Top_Artist;
use \Illuminate\Support\Collection;

final class TopArtistRepositories
{
    public function __construct(private Top_Artist $topArtistModel)
    {
    }

    /**
     * 集計を取ってる日付を取得
     * @param bool $isFirst
     * @return string
     */
    public function getAggregateDate(bool $isFirst = false): string
    {
        $aggregate_date = $this->topArtistModel
            ->groupBy('target_date')
            ->latest('target_date')
            ->pluck('target_date');

        return $isFirst ? $aggregate_date->first() : $aggregate_date;
    }

    /**
     * 集計日からアーティストを取得
     * @param string $aggregateDate
     * @param bool $isRand
     * @return Collection
     */
    public function getTopArtistByAggregateDate(string $aggregateDate, bool $isRand = false): Collection
    {
        $topArtist = $this->topArtistModel
            ->join('artist', 'top_artist.artist_id', '=', 'artist.id')
            ->where('target_date', $aggregateDate);

        $topArtist = $isRand ? $topArtist->inRandomOrder() : $topArtist->latest('target_date');
        return $topArtist->get();
    }
}
