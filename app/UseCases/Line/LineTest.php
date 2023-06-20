<?php

namespace App\UseCases\Line;

use App\Domains\FeaturePlayList;

final class LineTest
{
    public function __construct(
        private FeaturePlayList $featurePlayList
    ) {
    }

    public function __invoke(): string
    {
        return $this->featurePlayList->createFeaturePlaylistContent(1);
    }
}
