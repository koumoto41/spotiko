<?php

namespace App\Repositories;

use App\Models\Token;

final class TokenRepository
{
    public function __construct(private Token $tokenModel)
    {
    }

    /**
     * $userIdで絞り込んだToken情報を取得
     * @param int $userId
     * @return Token|null
     */
    public function getTokenByUserId(int $userId): ?Token
    {
        return $this->tokenModel->where('user_id', $userId)->first();
    }

    /**
     * Token情報をinsert
     * @param int $userId
     * @param string $accessToken
     * @param string $refreshToken
     * @return void
     */
    public function insertToken(int $userId, string $accessToken, string $refreshToken): void
    {
        $this->tokenModel = new Token();
        $this->tokenModel->user_id = $userId;
        $this->tokenModel->token = $accessToken;
        $this->tokenModel->refresh_token = $refreshToken;
        $this->tokenModel->save();
    }

    /**
     * Token情報を削除
     * @param int $userId
     * @return void
     */
    public function deleteToken(int $userId): void
    {
        $this->tokenModel->where('user_id', $userId)->delete();
    }
}
