<?php
namespace App\Repositories\Contracts;

interface CarReservationReportRepositoryInterface
{
    public function getByCar(int $carId);
    public function getByDateRange(string $from, string $to);
}
