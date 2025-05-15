<?php
namespace App\Repositories\Contracts;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\ScheduleException;
use Illuminate\Support\Collection;

interface ScheduleExceptionRepositoryInterface
{
    public function clearCache($trainerId);

   public function getAllForTrainerPaginated(int $trainerId): LengthAwarePaginator;
    public function getById(int $id): ?ScheduleException;
    public function create(array $data): ScheduleException;
    public function checkDateConflict(int $trainerId, string $date, ?int $ignoreId = null): bool;
}