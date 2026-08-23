<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Track\Repositories\TrackRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Track;

final class TrackRepository implements TrackRepositoryInterface
{
    public function __construct(private Track $trackModel)
    {
    }

    public function existTrack(string $spotifyId): bool
    {
        return $this->trackModel->where('spotify_id', $spotifyId)->exists();
    }

    public function getTrackIdBySpotifyId(string $spotifyId): ?int
    {
        $data = $this->trackModel->where('spotify_id', $spotifyId)->first();

        return $data?->id;
    }

    public function upsertTrack(array $trackArray): void
    {
        $rows = array_column($trackArray, 'track');

        if (!$rows) {
            return;
        }

        $this->trackModel->upsert(
            $rows,
            ['spotify_id'],
            ['artist_id', 'album_id', 'name', 'url', 'popularity', 'track_number', 'preview_url']
        );
    }
}
