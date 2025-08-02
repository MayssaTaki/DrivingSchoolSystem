<?php
namespace App\Repositories;

use App\Repositories\Contracts\CarReservationRepositoryInterface;
use App\Models\CarReservation;

class CarReservationRepository implements CarReservationRepositoryInterface
{
    public function create(array $data)
    {
        return CarReservation::create($data);
    }

   public function isCarReserved(int $carId, \DateTimeInterface $start, \DateTimeInterface $end): bool
{
    return CarReservation::where('car_id', $carId)
        ->where(function ($query) use ($start, $end) {
            $query->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
        })
        ->exists();
}
public function deleteBySessionId(int $sessionId): void
{
    CarReservation::where('session_id', $sessionId)->delete();
}

}
