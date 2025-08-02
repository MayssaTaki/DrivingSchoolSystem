<?php
namespace App\Repositories;

use App\Models\CarReservationReport;
use App\Repositories\Contracts\CarReservationReportRepositoryInterface;

class CarReservationReportRepository implements CarReservationReportRepositoryInterface
{
    public function getByCar(int $carId)
    {
        return CarReservationReport::where('car_id', $carId)->orderBy('date')->get();
    }

    public function getByDateRange(string $from, string $to)
    {
        return CarReservationReport::whereBetween('date', [$from, $to])->orderBy('date')->get();
    }
}
