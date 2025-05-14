<?php

namespace App\Repositories;
use Exception;
use App\Models\TrainingSchedule;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\TrainingSchedulesRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class TrainingSchedulesRepository  implements TrainingSchedulesRepositoryInterface
{
   public function getByTrainer($trainerId)
{
    $cacheKey = "trainer_schedules_{$trainerId}";

    return Cache::tags(['training_schedules', 'trainer_'.$trainerId])->remember($cacheKey, now()->addHours(1), function () use ($trainerId) {
        return TrainingSchedule::where('trainer_id', $trainerId)
            
            ->where(function($query) {
                $query->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', now());
            })
            ->orderByRaw("FIELD(day_of_week, 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday')")
            ->orderBy('start_time')
            ->paginate(10); 
    });
}
public function clearCache($trainerId)
{
    Cache::tags(['training_schedules'])->flush();
}
public function create(array $data)
{
    return TrainingSchedule::create($data);
}

public function update(int $id, array $data)
{
    $schedule = TrainingSchedule::findOrFail($id);
    $schedule->update($data);
    return $schedule;
}

public function changeStatus(int $id, string $status)
{
    $schedule = TrainingSchedule::findOrFail($id);
    $schedule->update(['status' => $status]);
    return $schedule;
}



}