<?php
namespace App\Repositories;

use App\Repositories\Contracts\PracticalExamRepositoryInterface;
use App\Models\PracticalExamSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class PracticalExamRepository implements PracticalExamRepositoryInterface
{
public function create(array $data): PracticalExamSchedule
    {
        return PracticalExamSchedule::create($data);
    }
    public function findByLicenseRequest(int $licenseRequestId): ?PracticalExamSchedule
{
    return PracticalExamSchedule::where('license_request_id', $licenseRequestId)->first();
}

 public function paginateLatest(int $perPage = 10): LengthAwarePaginator
    {
        return PracticalExamSchedule::latest()
            ->paginate($perPage);
    }

    public function getStudentSchedules(int $studentId, int $perPage = 10): LengthAwarePaginator
    {
        return PracticalExamSchedule::whereHas('licenseRequest', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->latest()
            ->paginate($perPage);
    }

        public function updateStatus(int $practicalId, string $status): bool
{
    return PracticalExamSchedule::where('id', $practicalId)
        ->update(['status' => $status]);
}

}