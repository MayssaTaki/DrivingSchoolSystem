<?php
namespace App\Repositories;

use App\Models\ScheduleException;
use App\Repositories\Interfaces\ScheduleExceptionRepositoryInterface;
use Illuminate\Support\Collection;

class ScheduleExceptionRepository implements ScheduleExceptionRepositoryInterface
{
    public function getAllForTrainer(int $trainerId): Collection
    {
        return ScheduleException::where('trainer_id', $trainerId)
            ->orderBy('exception_date', 'desc')
            ->get();
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