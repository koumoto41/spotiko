<?php

namespace App\Domain\Token\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Token;

interface TokenRepositoryInterface
{
    public function getTokenByUserId(int $userId): ?Token;

    public function insertToken(int $userId, string $accessToken, string $refreshToken): void;

    public function deleteToken(int $userId): void;
}
