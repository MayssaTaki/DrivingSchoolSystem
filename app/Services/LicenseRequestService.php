<?php
namespace App\Services;
use App\Services\Interfaces\LicenseRequestServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use App\Models\License;
use App\Repositories\Contracts\LicenseRequestRepositoryInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Events\ImageUploaded;

class LicenseRequestService implements LicenseRequestServiceInterface
{
protected LicenseRequestRepositoryInterface $licenseRepository;
protected LogServiceInterface $logService;
protected ActivityLoggerServiceInterface $activityLogger;
protected  TransactionServiceInterface $transactionService;

    public function __construct(LicenseRequestRepositoryInterface $licenseRepository
     ,LogServiceInterface $logService
        ,ActivityLoggerServiceInterface $activityLogger,TransactionServiceInterface $transactionService,)
    {
        $this->licenseRepository = $licenseRepository;
           $this->logService = $logService;
        $this->activityLogger = $activityLogger;
    }

public function requestLicense(array $data)
{
    try {
        $student = auth()->user()->student;
        $license = License::where('code', $data['license_code'])->firstOrFail();

        $this->checkConditions($student, $license, $data);

        $data['student_id'] = $student->id;
        $data['license_id'] = $license->id;

        $storedDocs = [];

        foreach ($data['required_documents'] as $file) {
            $path = $file->store('license_docs', 'public');
            $storedDocs[] = $path;

            $ext = strtolower($file->getClientOriginalExtension());
            $fullPath = storage_path('app/public/' . $path);

            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                event(new ImageUploaded($fullPath));
            } 
        }

       

        $licenseRequest = $this->licenseRepository->create($data);

        $this->activityLogger->log(
            'تم إضافة طلب رخصة جديدة',
            ['code' => $license->code],
            'license',
            $licenseRequest,
            auth()->user(),
            'request_license'
        );

        return $licenseRequest;

    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل إضافة طلب رخصة', [
            'message' => $e->getMessage(),
            'data' => $data,
            'trace' => $e->getTraceAsString(),
        ]);

        throw $e;
    }
}




     protected function checkConditions($student, $license, $data)
{
    $reqs = $license->requirements;

    if (isset($reqs['nationality']) && $student->nationality !== $reqs['nationality']) {
        throw new \Exception("الجنسية يجب أن تكون {$reqs['nationality']}.");
    }

    if (isset($reqs['allowed_for_military']) && !$reqs['allowed_for_military'] && $student->is_military) {
        throw new \Exception("لا يُسمح للعسكريين بالتقديم على هذه الرخصة.");
    }

    if ($license->min_age && $student->calculateAge() < $license->min_age) {
        throw new \Exception("العمر غير كافٍ للتقديم على هذه الرخصة.");
    }

}

public function getAllRequests(int $perPage = 10)
{
    return $this->licenseRepository->getAllPaginated($perPage);
}

public function getRequestsForCurrentStudent()
{
    $student = auth()->user()->student;
    return $this->licenseRepository->getByStudent($student->id);
}

public function approveRequest(int $requestId): bool
{
    try {
        $licenseRequest = $this->licenseRepository->findById($requestId);

        if (Gate::denies('approve', $licenseRequest)) {
            throw new AuthorizationException('ليس لديك صلاحية الموافقة على الرخصة.');
        }

        $this->licenseRepository->updateStatus($requestId, 'approved');

        $this->activityLogger->log(
            'تمت الموافقة على طلب رخصة',
            ['request_id' => $licenseRequest->id, 'license_code' => $licenseRequest->license->code],
            'license_request',
            $licenseRequest,
            auth()->user(),
            'approve_license'
        );

        return true;

    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في الموافقة على طلب رخصة', [
            'request_id' => $requestId,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        throw $e;
    }
}




public function rejectRequest(int $requestId, string $reason): bool
{
    try {
        $licenseRequest = $this->licenseRepository->findById($requestId);

        if (Gate::denies('reject', $licenseRequest)) {
            throw new AuthorizationException('ليس لديك صلاحية رفض الرخصة.');
        }

        $this->licenseRepository->updateStatus($requestId, 'rejected', $reason);

        $this->activityLogger->log(
            'تم رفض طلب رخصة',
            ['request_id' => $licenseRequest->id, 'reason' => $reason],
            'license_request',
            $licenseRequest,
            auth()->user(),
            'reject_license'
        );

        return true;

    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في رفض طلب رخصة', [
            'request_id' => $requestId,
            'reason' => $reason,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        throw $e;
    }
}

public function getPendingRequests(): LengthAwarePaginator
    {
        return $this->licenseRepository->findByStatus('pending');
    }

    public function getApprovedRequests(): LengthAwarePaginator
    {
        return $this->licenseRepository->findByStatus('approved');
    }

    public function getRejectedRequests(): LengthAwarePaginator
    {
        return $this->licenseRepository->findByStatus('rejected');
    }

    public function countPendingRequests()
    {
        return $this->licenseRepository->countByStatus('pending');
    }

    public function countApprovedRequests()
    {
        return $this->licenseRepository->countByStatus('approved');
    }

    public function countRejectedRequests()
    {
        return $this->licenseRepository->countByStatus('rejected');
    }
public function getMonthlyReport(int $year, ?string $licenseCode, ?string $status): Collection
    {
        return $this->licenseRepository->monthlyCounts($year, $licenseCode, $status);
    }
      public function getTypeReport(): Collection
    {
        return $this->licenseRepository->typeStatistics();
    }
    public function getMostRequestedLicenses(int $limit = 2): Collection
{
    return $this->licenseRepository->mostRequestedLicenses($limit);
}

}