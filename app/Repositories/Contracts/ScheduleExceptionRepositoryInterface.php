<?php
namespace App\Repositories\Interfaces;

use App\Models\ScheduleException;
use Illuminate\Support\Collection;

interface ScheduleExceptionRepositoryInterface
{
    public function getAllForTrainer(int $trainerId): Collection;
    public function getById(int $id): ?ScheduleException;
    public function create(array $data): ScheduleException;
    public function checkDateConflict(int $trainerId, string $date, ?int $ignoreId = null): bool;
}