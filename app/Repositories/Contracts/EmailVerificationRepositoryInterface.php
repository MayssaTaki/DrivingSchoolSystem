<?php
namespace App\Repositories\Contracts;

interface EmailVerificationRepositoryInterface
{
public function create(string $email, string $code, \Carbon\Carbon $expiresAt): void;
    public function getByEmail(string $email);
    public function delete(string $email): void;
}
