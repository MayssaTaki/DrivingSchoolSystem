<?php
namespace App\Services;

use App\Repositories\Contracts\CarReservationRepositoryInterface;
use App\Services\Interfaces\CarReservationServiceInterface;
use Illuminate\Support\Carbon;

class CarReservationService implements CarReservationServiceInterface
{
    protected $reservationRepo;

    public function __construct(CarReservationRepositoryInterface $reservationRepo)
    {
        $this->reservationRepo = $reservationRepo;
    }

    public function createReservation(array $data)
    {
        return $this->reservationRepo->create($data);
    }
  
    public function checkAvailability(int $carId, string $date, string $startTime, string $endTime): bool
    {
        $start = Carbon::parse("$date $startTime");
        $end = Carbon::parse("$date $endTime");

        return !$this->reservationRepo->isCarReserved($carId, $start, $end);
    }
}
