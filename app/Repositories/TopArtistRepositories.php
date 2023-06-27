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

    /**
     * 該当の日付のデータがあるか調べる
     * @param $targetDate
     * @return bool
     */
    public function existTargetDateData($targetDate): bool
    {
        return (bool)$this->topArtistModel->where('target_date', $targetDate)->first();
    }

    /**
     * 該当の日付のデータを削除
     * @param $targetDate
     * @return void
     */
    public function deleteTopArtistByTargetDate($targetDate): void
    {
        $this->topArtistModel->where('target_date', $targetDate)->delete();
    }

    /**
     * Artist Top Rate登録
     * @param $targetDate
     * @param $artistId
     * @param $popularity
     * @param $point
     * @return void
     */
    public function insertTopArtist($targetDate, $artistId, $popularity, $point): void
    {
        $upd_data = [
            'target_date' => $targetDate,
            'artist_id' => $artistId,
            'popularity' => $popularity,
            'point' => $point,
        ];

        $this->topArtistModel->fill($upd_data);
        $this->topArtistModel->save();
    }
}
