<?php
namespace App\Repositories;
use App\Repositories\Contracts\ScheduleExceptionRepositoryInterface;
use App\Models\ScheduleException;

class ScheduleExceptionRepository implements ScheduleExceptionRepositoryInterface
{
    public function create(array $data): ScheduleException
    {
        return ScheduleException::create($data);
    }

    public function findByTrainerAndDate(int $trainerId, string $date): ?ScheduleException
    {
        return ScheduleException::where('trainer_id', $trainerId)
            ->whereDate('exception_date', $date)
            ->first();
    }

    public function update(ScheduleException $exception, array $data): bool
    {
        return $exception->update($data);
    }

    public function delete(ScheduleException $exception): bool
    {
        return $exception->delete();
    }
    public function find(int $id): ?ScheduleException
{
    return ScheduleException::find($id);
}

}
