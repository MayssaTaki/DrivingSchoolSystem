<?php

namespace App\Repositories;

use App\Models\Car;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\CarRepositoryInterface;

class CarRepository implements CarRepositoryInterface
{
   

    public function getAllCars(?string $make, int $perPage = 10)
    {
        $page = request()->get('page', 1);
        $cacheKey = "cars_page_{$page}_make_" . ($make ?? 'all');
    
        return Cache::tags(['cars'])->remember($cacheKey, now()->addMinutes(10), function () use ($make, $perPage) {
            $query = Car::query(); 
    
            if ($make) {
                $query->where('make', 'like', "%{$make}%");
            }
    
            return $query->paginate($perPage);
        });
    }
    public function clearCache()
    {
        Cache::tags(['cars'])->flush();
    }
    public function countCars(): int
    {
        return Cache::tags(['cars'])->remember('cars_count', now()->addMinutes(5), function () {
            return Car::count();
        });
    }
    public function create(array $data): Car
    {
        return Car::create($data);
    }
    public function deleteById(int $id): bool
    {
        return Car::destroy($id) > 0;
    }
     public function findById(int $id): ?Car
    {
        return Car::find($id);
    }
    public function update(Car $car, array $data): Car
    {
        $car->update($data);
        return $car;
    }


public function getFirstAvailableForSession(string $date, string $time)
{
    return Car::where('status', 'available')->first();
}


    public function updateStatus(int $carId, string $status): bool
{
    return Car::where('id', $carId)
        ->update(['status' => $status]);
}

 public function isCarAvailable(int $carId): bool
    {
        $car= Car::find($carId);
        return $car && $car->status === 'available';
    }
public function getAvailableCars()
{
    return Car::where('status', 'available')->get();
}


public function isCarBook(int $carId): bool
    {
        $car= Car::find($carId);
        return $car && $car->status === 'booked';
    }

       public function find(int $id)
{
    return Car::findOrFail($id);
}
public function findWithLock(int $id)
{
    return Car::where('id', $id)->lockForUpdate()->firstOrFail();
}

}