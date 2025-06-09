<?php
namespace App\Repositories;
use App\Repositories\Contracts\ScheduleExceptionRepositoryInterface;
use App\Models\ScheduleException;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;


class ScheduleExceptionRepository implements ScheduleExceptionRepositoryInterface
{
    public function create(array $data): ScheduleException
    {
        return ScheduleException::create($data);
    }

public function clearCache()
  {
      Cache::tags(['trainer_exceptions'])->flush();
  }

public function findAllByTrainer(int $trainerId): LengthAwarePaginator
{
    $page = request()->get('page', 1);
    $cacheKey = "trainer_exceptions_{$trainerId}_page_{$page}";

    return Cache::tags(['trainer_exceptions'])->remember($cacheKey, now()->addMinutes(10), function () use ($trainerId) {
        return ScheduleException::where('trainer_id', $trainerId)->paginate(10);
    });
}

public function findAll(): LengthAwarePaginator
{
    $page = request()->get('page', 1);
    $cacheKey = "trainer_exceptions_all_page_{$page}";

    return Cache::tags(['trainer_exceptions'])->remember($cacheKey, now()->addMinutes(10), function () {
        return ScheduleException::paginate(10);
    });
}

    public function findByStatus(string $status): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $cacheKey = "trainer_exceptions_{$status}_page_{$page}";

        return Cache::tags(['trainer_exceptions'])->remember($cacheKey, now()->addMinutes(10), function () use ($status) {
            return ScheduleException::where('status', $status)->paginate(10);
        });
    }



   
    public function find(int $id): ?ScheduleException
{
    return ScheduleException::find($id);
}

}
