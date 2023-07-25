<?php

namespace App\UseCases;

use App\Services\SpotifyApi;
use SpotifyWebAPI\Session;

final class GetSpotifyAccessToken
{
    public function __construct(
        private SpotifyApi $spotifyApi,
        private Session $spotifyApiSession,
    ) {
    }

    public function __invoke(string $code, int $userId): void
    {
        // 承認コード使ってトークンをリクエスト
        $this->spotifyApiSession->requestAccessToken($code);

        // トークン登録
        $this->spotifyApi->saveAccessToken($userId);
    }
}
