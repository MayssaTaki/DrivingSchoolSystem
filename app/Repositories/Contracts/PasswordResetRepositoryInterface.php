<?php

namespace App\Repositories\Contracts;

interface PasswordResetRepositoryInterface
{
    public function storeResetToken(string $email, string $token);
    
    public function getByEmail(string $email);

    public function delete(string $email);
    
}