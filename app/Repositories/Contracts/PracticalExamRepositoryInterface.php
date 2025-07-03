<?php
namespace App\Repositories\Contracts;
use App\Models\PracticalExamSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PracticalExamRepositoryInterface
{

        public function create(array $data): PracticalExamSchedule;
    public function findByLicenseRequest(int $licenseRequestId): ?PracticalExamSchedule;
        public function paginateLatest(int $perPage = 10): LengthAwarePaginator;
    public function getStudentSchedules(int $studentId, int $perPage = 10): LengthAwarePaginator;
    public function updateStatus(int $practicalId, string $status): bool;
public function findById(int $id): PracticalExamSchedule;
public function countByStatus(string $from, string $to): array;
    public function failedOrAbsentStudents(string $from, string $to): array;
    public function successRatio(string $from, string $to): float;
}