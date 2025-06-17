<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface UserServiceInterface
{
    
    public function register(array $data): User;

   
    public function delete(int $id): void;

    
    public function update(User $user, array $data): User;
}
