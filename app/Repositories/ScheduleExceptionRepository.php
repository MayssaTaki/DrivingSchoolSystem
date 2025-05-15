<?php
namespace App\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\ScheduleException;
use App\Repositories\Contracts\ScheduleExceptionRepositoryInterface;
use Illuminate\Support\Collection;

class ScheduleExceptionRepository implements ScheduleExceptionRepositoryInterface
{
   public function getAllForTrainerPaginated(int $trainerId): LengthAwarePaginator
{
    $cacheKey = "schedule_exceptions_trainer_{$trainerId}_page_" . request('page', 1);

    return Cache::tags(['schedule_exceptions', 'trainer_'.$trainerId])
        ->remember($cacheKey, now()->addHours(1), function () use ($trainerId) {
            return ScheduleException::where('trainer_id', $trainerId)
                ->orderBy('exception_date', 'desc')
                ->paginate(10); 
        });
}
public function clearCache($trainerId)
{
    Cache::tags(['schedule_exceptions'])->flush();
}
    public function getById(int $id): ?ScheduleException
    {
        return ScheduleException::find($id);
    }

    public function create(array $data): ScheduleException
    {
        return ScheduleException::create($data);
    }

    

    public function checkDateConflict(int $trainerId, string $date, ?int $ignoreId = null): bool
    {
        $query = ScheduleException::where('trainer_id', $trainerId)
            ->where('exception_date', $date);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}