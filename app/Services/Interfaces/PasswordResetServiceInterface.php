<?php

namespace App\Services\Interfaces;

interface PasswordResetServiceInterface
{
    public function sendResetLink(string $email): void;

    public function resetPassword(array $data): void;

    public function verifyCode(string $email, string $code): bool;

    public function resendCode(string $email): void;
}
