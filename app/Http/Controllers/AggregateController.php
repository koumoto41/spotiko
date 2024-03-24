<?php

namespace App\Http\Controllers;

use App\UseCases\Aggregate\Artist;
use App\UseCases\Aggregate\Track;

class AggregateController extends Controller
{
    private const USER_ID = 1;

    public function __construct()
    {
    }

    /**
     * TOP ARTISTの取得
     */
    public function aggregateArtist(Artist $aggregateArtist): void
    {
        $aggregateArtist(self::USER_ID);
        echo '集計が終了しました';
    }

    /**
     * TOP Trackの取得
     */
    public function aggregateTrack(Track $aggregateTrack): void
    {
        $aggregateTrack(self::USER_ID);
        echo '集計が終了しました';
    }
}
