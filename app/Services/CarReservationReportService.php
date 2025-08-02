<?php
namespace App\Services;

use App\Repositories\Contracts\CarReservationReportRepositoryInterface;
use App\Services\Interfaces\CarReservationReportServiceInterface;

class CarReservationReportService implements CarReservationReportServiceInterface
{
    protected $repo;

    public function __construct(CarReservationReportRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getCarReport(int $carId)
    {
        return $this->repo->getByCar($carId);
    }

    public function getReportByDateRange(string $from, string $to)
    {
        return $this->repo->getByDateRange($from, $to);
    }
}
