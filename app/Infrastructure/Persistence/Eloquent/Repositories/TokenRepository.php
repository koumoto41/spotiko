<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Token\Repositories\TokenRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Token;

final class TokenRepository implements TokenRepositoryInterface
{
    public function __construct(private Token $tokenModel)
    {
    }

    public function getTokenByUserId(int $userId): ?Token
    {
        return $this->tokenModel->where('user_id', $userId)->first();
    }

    public function insertToken(int $userId, string $accessToken, string $refreshToken): void
    {
        $this->tokenModel->newInstance()->fill([
            'user_id' => $userId,
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
        ])->save();
    }

    public function deleteToken(int $userId): void
    {
        $this->tokenModel->where('user_id', $userId)->delete();
    }
}
