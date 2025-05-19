<?php
namespace App\Repositories\Contracts;

interface BookingRepositoryInterface
{
    public function create(array $data);
    public function isSessionAvailable(int $sessionId): bool;
}
