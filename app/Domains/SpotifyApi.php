<?php

namespace App\Domains;

use App\Repositories\TokenRepositories;
use JetBrains\PhpStorm\NoReturn;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

final class SpotifyApi
{
    public function __construct(
        private TokenRepositories $tokenRepositories,
        private Session $spotifyApiSession,
        private SpotifyWebAPI $spotifyWebApi
    ) {
        $this->init();
    }

    /**
     * 初期設定
     * @return void
     */
    private function init(): void
    {
        // セッションオブジェクトを作成
        $this->spotifyApiSession = new Session(
            Config('spotify.CLIENT_ID'),
            Config('spotify.CLIENT_SECRET'),
            Config('spotify.REDIRECT_URI')
        );
    }

    /**
     * access tokenをDBに登録
     * @param int $userId
     * @return void
     */
    public function saveAccessToken(int $userId): void
    {
        if ($this->tokenRepositories->getTokenByUserId($userId)) {
            // データがあったらdelete
            $this->tokenRepositories->deleteToken($userId);
        }
        // tokenを登録
        $this->tokenRepositories->insertToken(
            $userId,
            $this->spotifyApiSession->getAccessToken(),
            $this->spotifyApiSession->getRefreshToken()
        );
    }

    /**
     * 承認コード取得のため、承認URLへアクセスする
     * @return void
     */
    #[NoReturn] public function accessAuthorizeUrl(): void
    {
        # scopeを設定し、承認URLへアクセス
        header('Location: ' . $this->spotifyApiSession->getAuthorizeUrl(['scope' => Config('spotify.scope')]));
        die();
    }
}





