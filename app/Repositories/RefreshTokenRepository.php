<?php

namespace App\Repositories;

use App\Models\RefreshToken;
use App\Repositories\Contracts\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function create(array $data): RefreshToken
    {
        return RefreshToken::create($data);
    }

    public function findValidToken(string $hashedToken): ?RefreshToken
    {
        return RefreshToken::where('token', $hashedToken)
                           ->where('expires_at', '>', now())
                           ->first();
    }

    public function deleteByUserId(int $userId): void
    {
        RefreshToken::where('user_id', $userId)->delete();
    }
}
