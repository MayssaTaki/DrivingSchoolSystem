<?php
namespace App\Repositories;
use Illuminate\Support\Facades\DB;

use App\Models\CarLocation;
use App\Repositories\Contracts\CarLocationRepositoryInterface;

class CarLocationRepository implements CarLocationRepositoryInterface
{
    public function create(array $data)
    {
        return CarLocation::create($data);
    }

    public function getLatestForCar(int $carId)
    {
        return CarLocation::where('car_id', $carId)->latest('recorded_at')->first();
    }
    public function getLocationsForCar(int $carId)
    {
        return CarLocation::where('car_id', $carId)
            ->orderBy('recorded_at', 'asc')
            ->get();
    }

   public function getLastLocationsForActiveCars()
{
    return CarLocation::select('car_locations.*')
        ->join(DB::raw('(
            SELECT car_id, MAX(recorded_at) as latest_time
            FROM car_locations
            GROUP BY car_id
        ) as latest'), function ($join) {
            $join->on('car_locations.car_id', '=', 'latest.car_id')
                 ->on('car_locations.recorded_at', '=', 'latest.latest_time');
        })
        ->get();
}
}
