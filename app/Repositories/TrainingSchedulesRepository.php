<?php

namespace App\Repositories;

use App\Models\TrainingSchedule;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\TrainingSchedulesRepositoryInterface;

class TrainingSchedulesRepository implements TrainingSchedulesRepositoryInterface
{
    public function getByTrainer($trainerId)
{
    $cacheKey = "trainer_schedules_{$trainerId}";

    return Cache::tags(['training_schedules', 'trainer_' . $trainerId])->remember($cacheKey, now()->addHours(1), function () use ($trainerId) {
        return TrainingSchedule::where('trainer_id', $trainerId)
            ->orderByRaw("FIELD(day_of_week, 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday')")
            ->orderBy('start_time')
            ->paginate(10);
    });
}


    public function clearCache($trainerId)
    {
        $cacheKey = "trainer_schedules_{$trainerId}";
        Cache::tags(['training_schedules', 'trainer_' . $trainerId])->forget($cacheKey);
    }

    public function create(array $data)
    {
        return TrainingSchedule::create($data);
    }

   public function scheduleExists(array $criteria): bool
{
    return TrainingSchedule::where('trainer_id', $criteria['trainer_id'])
        ->where('day_of_week', $criteria['day_of_week'])
        ->where('start_time', $criteria['start_time'])
        ->exists();
}


    public function changeStatus(int $id, string $status)
    {
        $schedule = TrainingSchedule::findOrFail($id);
        $schedule->update(['status' => $status]);
        return $schedule;
    }
    public function findById(int $id): TrainingSchedule
{
    return TrainingSchedule::findOrFail($id);
}

}
