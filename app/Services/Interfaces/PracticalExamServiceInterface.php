<?php
namespace App\Services\Interfaces;
use App\Models\PracticalExamSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PracticalExamServiceInterface
{
public function scheduleExam(array $data): PracticalExamSchedule;
public function listAll(int $perPage = 10): LengthAwarePaginator;
public function getMySchedules(int $perPage = 10): LengthAwarePaginator;
public function markAsPassed(int $id): bool;
public function markAsFailed(int $id): bool;
public function markAsAbsent(int $id): bool;
public function getCountByStatus(array $filters): array;
public function getFailedOrAbsentStudents(array $filters): array;
 public function getSuccessRatio(array $filters): float;
}