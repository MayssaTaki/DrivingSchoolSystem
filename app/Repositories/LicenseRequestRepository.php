<?php
namespace App\Repositories;
use App\Repositories\Contracts\LicenseRequestRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use App\Enums\LicenseType;
use App\Models\LicenseRequest;
use App\Models\License;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LicenseRequestRepository implements LicenseRequestRepositoryInterface
{
  public function create(array $data): LicenseRequest
{
    $storedDocs = [];

    foreach ($data['required_documents'] as $file) {
        $storedDocs[] = $file->store('license_docs', 'public');
    }

    return LicenseRequest::create([
        'student_id' => $data['student_id'],
        'license_id' => $data['license_id'],
        'type' => $data['type'],
        'status' => 'pending',
        'document_files' => $storedDocs,
        'notes' => $data['notes'] ?? null,
    ]);
}


public function getAllPaginated(int $perPage = 10)
{
    return LicenseRequest::with('license', 'student.user')
        ->orderByDesc('created_at')
        ->paginate($perPage);
}

public function getByStudent(int $studentId)
{
    return LicenseRequest::with('license')
        ->where('student_id', $studentId)
        ->orderByDesc('created_at')
        ->get();
}

 public function updateStatus(int $requestId, string $status, ?string $notes = null): bool
{
    $data = ['status' => $status];

    if ($status === 'approved') {
        

               $licenseRequest = LicenseRequest::findOrFail($requestId);

$data['notes'] = "يتم تحديد موعد الفحص خلال {$licenseRequest->license->requirements['exam_schedule_days']} يومًا من تاريخ قبول طلب التقديم على الشهادة.";
    }

    if ($notes) {
        $data['notes'] = $notes;
    }

    return LicenseRequest::where('id', $requestId)->update($data);
}

   public function findById(int $id): LicenseRequest
{
    return LicenseRequest::findOrFail($id);
}


public function findByStatus(string $status): LengthAwarePaginator
    {
    return LicenseRequest::where('status', $status)->paginate(10);
    }
public function countByStatus(string $status)
    {
    return LicenseRequest::where('status', $status)->count();
    }

     public function monthlyCounts(int $year, ?string $licenseCode = null, ?string $status = null): Collection
    {
        $query = LicenseRequest::selectRaw("MONTH(created_at) as month, COUNT(*) as count")
            ->whereYear('created_at', $year);

        if ($licenseCode) {
            $query->whereHas('license', fn($q)=>$q->where('code',$licenseCode));
        }
        if ($status) {
            $query->where('status',$status);
        }
        return $query->groupBy('month')->orderBy('month')->get();
    }

         public function typeStatistics(): Collection
    {
        return LicenseRequest::selectRaw("
            license_id,
            COUNT(*) as total,
            SUM(status='approved') as approved,
            SUM(status='rejected') as rejected
        ")->with('license:id,code,name')
        ->groupBy('license_id')
        ->get()
        ->map(fn($row)=>[
            'license' => $row->license,
            'total' => $row->total,
            'approved' => $row->approved,
            'rejected' => $row->rejected,
            'approved_pct' => round($row->approved/$row->total*100,2),
            'rejected_pct' => round($row->rejected/$row->total*100,2),
        ]);
    }

    public function mostRequestedLicenses(int $limit = 2): Collection
{
    return LicenseRequest::selectRaw('license_id, COUNT(*) as total')
        ->groupBy('license_id')
        ->orderByDesc('total')
        ->with('license:id,name,code')
        ->limit($limit)
        ->get()
        ->map(function ($row) {
            return [
                'license_code' => $row->license->code,
                'license_name' => $row->license->name,
                'total_requests' => $row->total,
            ];
        });
}

}