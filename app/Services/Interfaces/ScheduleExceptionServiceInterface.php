<?php

namespace App\Services\Interfaces;

use App\Models\ScheduleException;
use Illuminate\Pagination\LengthAwarePaginator;

interface ScheduleExceptionServiceInterface
{
    public function createExceptions(int $trainerId, array $dates, ?string $reason = null): array;

    public function approveException(int $exceptionId): ?ScheduleException;

    public function rejectException(int $exceptionId): ?ScheduleException;

    public function clearExceptionCache(): void;

    public function getAllExceptionsByTrainer(int $trainerId): LengthAwarePaginator;

    public function getAllTrainersExceptions(): LengthAwarePaginator;

    public function getPendingExceptions(): LengthAwarePaginator;

    public function getApprovedExceptions(): LengthAwarePaginator;

    public function getRejectedExceptions(): LengthAwarePaginator;
}
