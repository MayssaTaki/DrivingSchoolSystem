<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface EmailVerificationServiceInterface
{
    public function sendVerificationCode(User $user): void;

    public function verifyCode(User $user, string $code): void;

    public function verifyCodeById(int $userId, string $code): void;

    public function resendVerificationCodeById(int $userId): void;

    public function sendCustomEmail(User $user, string $subject, string $html);
}
