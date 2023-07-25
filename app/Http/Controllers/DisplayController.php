<?php

namespace App\Http\Controllers;

use App\Services\FeaturePlayListService;

class DisplayController extends Controller
{
    public function lineTest(FeaturePlayListService $featurePlayList): void
    {
        var_dump($featurePlayList->createFeaturePlaylistContent(1));
    }
}
