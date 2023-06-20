<?php

namespace App\UseCases\Top;

use App\Repositories\TopArtistRepositories;
use App\Repositories\TopTrackRepositories;

final class GetList
{
    public function __construct(
        private TopArtistRepositories $topArtistRepositories,
        private TopTrackRepositories $topTrackReposition,
    ) {
    }

    public function __invoke(): array
    {
        $displayArtistDate = $this->topArtistRepositories->getAggregateDate(true);
        $displayTrackDate = $this->topTrackReposition->getAggregateDate(true);

        return [
            'display_artist_date' => $displayArtistDate,
            'artist' => $this->topArtistRepositories->getTopArtistByAggregateDate($displayArtistDate),
            'display_track_date' => $displayTrackDate,
            'track' => $this->topTrackReposition->getTopTrackByAggregateDate($displayTrackDate)
        ];
    }
}
