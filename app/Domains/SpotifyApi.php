<?php

namespace App\Domains;

use App\Repositories\TokenRepositories;
use JetBrains\PhpStorm\NoReturn;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Exception;

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

    /**
     * アクセストークンをrefreshして登録する
     * @param int $userId
     * @return void
     */
    public function refreshAccessToken(int $userId): void
    {
        // ここでDBからトークン取ってくる
        $tokenData = $this->tokenRepositories->getTokenByUserId($userId);
        $oldAccessToken = $tokenData->refresh_token;

        // トークンをリフレッシュして取得する
        $this->spotifyApiSession->refreshAccessToken($oldAccessToken);

        // トークン登録
        $this->saveAccessToken($userId);
    }

    /**
     * @param Exception $exception
     * @param int $userId
     * @return bool
     */
    public function checkRefreshToken(Exception $exception, int $userId): bool
    {
        $message = $exception->getMessage();
        $code = $exception->getCode();

        // tokenの期限切れなのでリフレッシュする
        if ($code === 401 && $message === 'The access token expired') {
            $this->refreshAccessToken($userId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * トークンをセットする
     * @param int $userId
     * @return void
     */
    private function setAccessToken(int $userId): void
    {
        $this->refreshAccessToken($userId);

        $tokenData = $this->tokenRepositories->getTokenByUserId($userId);
        $this->spotifyWebApi->setAccessToken($tokenData->token);
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFeaturePlaylist(int $userId, int $limit = 20, int $offset = 0): array
    {
        $this->setAccessToken($userId);
        return $this->spotifyWebApi->getFeaturedPlaylists([
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * @param int $userId
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @param string $time_range
     * @return array
     */
    public function getMyTop(
        int $userId,
        string $type,
        int $limit = 100,
        int $offset = 0,
        string $time_range = 'short_term'
    ): array {
        $this->setAccessToken($userId);

        return $this->api->getMyTop($type, [
            'limit' => $limit,
            'offset' => $offset,
            'time_range' => $time_range
        ]);
    }
}
