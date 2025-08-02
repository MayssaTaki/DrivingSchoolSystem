<?php
namespace App\Services\Interfaces;

interface CarReservationReportServiceInterface
{
    public function getCarReport(int $carId);
    public function getReportByDateRange(string $from, string $to);
}
