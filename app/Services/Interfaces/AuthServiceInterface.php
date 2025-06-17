<?php
namespace App\Services\Interfaces;
use Illuminate\Pagination\LengthAwarePaginator;

interface AuthServiceInterface
{

        public function login(array $credentials): array;
            public function refreshToken(): array;
        public function logoutUser(): void;        
}
