<?php

namespace App\Repositories;

use App\Models\CarFault;
use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\CarFaultRepositoryInterface;

class CarFaultRepository implements CarFaultRepositoryInterface
{
    public function create(array $data)
    {
        return CarFault::create($data);
    }
public function getAllLatest()
{
    $page = request()->get('page', 1);
    $cacheKey = "car_faults_all_page_{$page}";

    return Cache::tags(['car_faults'])->remember($cacheKey, now()->addMinutes(10), function () {
        return CarFault::with(['car', 'trainer', 'booking'])
            ->latest()
            ->paginate(10);
    });
}

public function getFaultsByTrainer($trainerId)
{
    $page = request()->get('page', 1);
    $cacheKey = "car_faults_trainer_{$trainerId}_page_{$page}";

    return Cache::tags(['car_faults'])->remember($cacheKey, now()->addMinutes(10), function () use ($trainerId) {
        return CarFault::with(['car', 'trainer', 'booking.session'])
            ->where('trainer_id', $trainerId)
            ->latest()
            ->paginate(10);
    });
}
public function clearFaultsCache()
{
    Cache::tags(['car_faults'])->flush();
}
public function updateStatus(int $faultId, string $status): bool
{
    return CarFault::where('id', $faultId)->update(['status' => $status]);
}

public function findWithLock(int $faultId): CarFault
{
    return CarFault::where('id', $faultId)->lockForUpdate()->firstOrFail();
}
  public function isFaultProgress(int $faultId): bool
    {
        $car= CarFault::find($faultId);
        return $car && $car->status === 'in_progress';
    }
     public function isFaultNew(int $faultId): bool
    {
        $car= CarFault::find($faultId);
        return $car && $car->status === 'new';
    }

    public function countFaultsPerCar()
{
    return Car::withCount('faultReports')->get(['id', 'make', 'model']);
}

public function getTopFaultedCars(int $limit = 5)
{
    return Car::withCount('faultReports')
        ->having('fault_reports_count', '>', 0)
        ->orderByDesc('fault_reports_count')
        ->take($limit)
        ->get(['id', 'make', 'model']);
}


public function getMonthlyFaultsCount(int $year = null)
{
    $year = $year ?? now()->year;

    return CarFault::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
        ->whereYear('created_at', $year)
        ->groupByRaw('MONTH(created_at)')
        ->orderBy('month')
        ->get();
}

public function getAverageMonthlyFaultsPerCar(int $year = null)
{
    $year = $year ?? now()->year;

   return Car::select('id', 'make', 'model')
    ->withCount(['faultReports as yearly_faults' => function ($query) use ($year) {
        $query->whereYear('created_at', $year);
    }])
    ->get()
    ->map(function ($car) use ($year) {
        $car->year = $year;
        $car->monthly_avg_faults = round($car->yearly_faults / 12, 2);
        return $car;
    });

}

public function getFaultsStatusCountPerCar()
{
    return Car::with(['faultReports' => function ($query) {
        $query->select('car_id', 'status', DB::raw('count(*) as count'))
            ->groupBy('car_id', 'status');
    }])->get(['id', 'make', 'model']);
}

}