<?php

namespace App\Repositories\Contracts;

use App\Models\RefreshToken;

interface RefreshTokenRepositoryInterface
{
    public function create(array $data): RefreshToken;
    public function findValidToken(string $hashedToken): ?RefreshToken;
    public function deleteByUserId(int $userId): void;
}
