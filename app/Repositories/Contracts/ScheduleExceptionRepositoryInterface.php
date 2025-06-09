<?php
namespace App\Repositories\Contracts;
use Illuminate\Pagination\LengthAwarePaginator;


use App\Models\ScheduleException;
use Illuminate\Support\Collection;

interface ScheduleExceptionRepositoryInterface
{     public function clearCache();
public function findAll(): LengthAwarePaginator;
    public function findByStatus(string $status): LengthAwarePaginator;

    public function find(int $id): ?ScheduleException;
  public function create(array $data): ScheduleException;
public function findAllByTrainer(int $trainerId): LengthAwarePaginator;  
}