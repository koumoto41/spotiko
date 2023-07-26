<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Repositories\TopArtistRepository;
use App\Repositories\TopTrackRepository;

class TopController extends Controller
{
    public function getIndex(
        TopArtistRepository $topArtistRepositories,
        TopTrackRepository $topTrackRepositories
    ): string {
        $displayArtistDate = $topArtistRepositories->getAggregateDate(true);
        $displayTrackDate = $topTrackRepositories->getAggregateDate(true);

        return view('top', [
            'display_artist_date' => $displayArtistDate,
            'artist' => $topArtistRepositories->getTopArtistByAggregateDate($displayArtistDate),
            'display_track_date' => $displayTrackDate,
            'track' => $topTrackRepositories->getTopTrackByAggregateDate($displayTrackDate)
        ]);
    }
}
