<?php
namespace App\Services\Interfaces;

interface CarReservationServiceInterface
{
    public function createReservation(array $data);

    public function checkAvailability(int $carId, string $date, string $startTime, string $endTime): bool;
}
