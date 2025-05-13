<?php
namespace App\Repositories\Contracts;

interface EmailVerificationRepositoryInterface
{
    public function create(string $email, string $code): void;
    public function getByEmail(string $email);
    public function delete(string $email): void;
}
