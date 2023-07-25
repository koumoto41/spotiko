<?php

namespace App\UseCases;

use App\Services\SpotifyApi;

final class GetSpotifyAuthorizationCode
{
    public function __construct(
        private SpotifyApi $spotifyApi,
    ) {
    }

    public function __invoke(): void
    {
        // 承認コードを取得
        $this->spotifyApi->accessAuthorizeUrl();
    }
}
