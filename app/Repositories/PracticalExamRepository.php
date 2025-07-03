<?php
namespace App\Repositories;

use App\Repositories\Contracts\PracticalExamRepositoryInterface;
use App\Models\PracticalExamSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use DB;

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
public function findById(int $id): PracticalExamSchedule
{
    return PracticalExamSchedule::findOrFail($id);
}

public function countByStatus(string $from, string $to): array
    {
        return PracticalExamSchedule::select('status', DB::raw('count(*) as count'))
            ->whereBetween('exam_date', [$from, $to])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function failedOrAbsentStudents(string $from, string $to): array
    {
        return PracticalExamSchedule::select('license_request_id', DB::raw('count(*) as occurrences'))
            ->with(['licenseRequest.student'])
            ->whereBetween('exam_date', [$from, $to])
            ->whereIn('status', ['failed', 'absent'])
            ->groupBy('license_request_id')
            ->get()
            ->map(fn($row) => [
                'student_id' => $row->licenseRequest->student->id,
                'student_name' => $row->licenseRequest->student->first_name . ' ' . $row->licenseRequest->student->last_name,
                'occurrences' => $row->occurrences,
            ])->toArray();
    }

    public function successRatio(string $from, string $to): float
    {
        $totals = PracticalExamSchedule::whereBetween('exam_date', [$from, $to])->count();
        if ($totals === 0) return 0.0;
        $passed = PracticalExamSchedule::whereBetween('exam_date', [$from, $to])
            ->where('status', 'passed')
            ->count();
        return round(($passed / $totals) * 100, 2);
    }
}