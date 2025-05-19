<?php
namespace App\Repositories\Contracts;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\ScheduleException;
use Illuminate\Support\Collection;

interface ScheduleExceptionRepositoryInterface
{
    public function find(int $id): ?ScheduleException;
  public function create(array $data): ScheduleException;
    public function findByTrainerAndDate(int $trainerId, string $date): ?ScheduleException;
    public function update(ScheduleException $exception, array $data): bool;
        public function delete(ScheduleException $exception): bool;
}