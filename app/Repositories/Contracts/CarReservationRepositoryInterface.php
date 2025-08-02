<?php
namespace App\Repositories\Contracts;

interface CarReservationRepositoryInterface
{
    public function create(array $data);
public function deleteBySessionId(int $sessionId): void;
    public function isCarReserved(int $carId, \DateTimeInterface $start, \DateTimeInterface $end): bool;
}
