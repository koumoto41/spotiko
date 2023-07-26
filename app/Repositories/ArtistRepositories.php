<?php

namespace App\Repositories;

use App\Models\Artist;
use Illuminate\Support\Collection;

final class ArtistRepositories
{
    public function __construct(private Artist $artistModel)
    {
    }

    /**
     * @param int $spotifyId
     * @return bool
     */
    public function existArtist(int $spotifyId): bool
    {
        return $this->artistModel->where('spotify_id', $spotifyId)->exists();
    }

    /**
     * spotifyIdからArtistIdを検索して返す
     * @param $spotifyId
     * @return int|null
     */
    public function getArtistIdBySpotifyId($spotifyId): ?int
    {
        $data = $this->artistModel->where('spotify_id', $spotifyId)->first();

        if ($data) {
            return $data->id;
        }
        return null;
    }

    /**
     * Artistがあれば更新、無ければ追加する
     * @param $artistArray
     */
    public function upsertArtist($artistArray): void
    {
        $this->artistModel->upsert($artistArray, ['spotify_id'], ['name', 'url', 'popularity', 'img_640', 'img_320', 'img_160']);
    }
}
