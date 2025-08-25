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
use App\Services\Interfaces\MtnPaymentClientServiceInterface;
use App\Repositories\Contracts\PaymentTransactionRepositoryInterface;
use App\Models\PracticalExamSchedule;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Events\ImageUploaded;
use App\Events\LicenseRequested;
use App\Events\LicenseRequestApproved;
use App\Events\LicenseRequestRejected;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Str;

class LicenseRequestService implements LicenseRequestServiceInterface
{
protected LicenseRequestRepositoryInterface $licenseRepository;
protected LogServiceInterface $logService;
protected ActivityLoggerServiceInterface $activityLogger;
protected  TransactionServiceInterface $transactionService;
protected FirebaseServiceInterface $firebaseservice;
protected MtnPaymentClientServiceInterface $paymentService;
protected PaymentTransactionRepositoryInterface $paymentRepo;

    public function __construct(LicenseRequestRepositoryInterface $licenseRepository
     ,LogServiceInterface $logService
        ,ActivityLoggerServiceInterface $activityLogger,TransactionServiceInterface $transactionService,
            FirebaseService $firebaseService,
                    MtnPaymentClientServiceInterface $paymentService,
PaymentTransactionRepositoryInterface $paymentRepo
            
)
    {
        $this->licenseRepository = $licenseRepository;
           $this->logService = $logService;
        $this->activityLogger = $activityLogger;
                    $this->firebaseService = $firebaseService;
        $this->paymentService = $paymentService;
        $this->paymentRepo = $paymentRepo;

    }

public function requestLicense(array $data)
{
    try {
        $student = auth()->user()->student;
        $license = License::where('code', $data['license_code'])->firstOrFail();
   if ((int)$data['amount'] !== (int)$license->registration_fee) {
            throw new \Exception("المبلغ المحدد لا يطابق سعر الرخصة: {$license->registration_fee}");
        }
        $this->checkConditions($student, $license, $data);

        $data['student_id'] = $student->id;
        $data['license_id'] = $license->id;

        $storedDocs = [];

        foreach ($data['required_documents'] as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $uniqueName = $originalName . '_' . Str::random(8);

            $uploaded = Cloudinary::uploadApi()->upload(
                $file->getRealPath(),
                [
                    'public_id' => 'license_docs/' . $uniqueName,
                    'quality' => 'auto:good',
                    'type' => 'authenticated'
                ]
            );

            $storedDocs[] = $uploaded['public_id'];

            
        }

        $data['required_documents'] = $storedDocs;

        $licenseRequest = $this->licenseRepository->create($data);
        $payment = $this->paymentService->createInvoice((int)$data['amount']); 
$transaction = $this->paymentRepo->findByInvoice($payment['invoiceId']);
$licenseRequest->update([
    'payment_transaction_id' => $transaction->id
]);

        event(new LicenseRequested($student, $license));

        $users = User::whereIn('role', ['employee', 'admin'])
            ->whereNotNull('fcm_token')
            ->get();

        foreach ($users as $user) {
            $this->firebaseService->sendNotification(
                $user->fcm_token,
                '📄 تم إضافة طلب رخصة جديدة',
                "تمت إضافة طلب رخصة جديدة بالكود: {$license->code}"
            );
        }

        $this->activityLogger->log(
            'تم إضافة طلب رخصة جديدة',
            ['code' => $license->code],
            'license',
            $licenseRequest,
            auth()->user(),
            'request_license'
        );
return [
        'licenseRequest' => $licenseRequest,
        'payment' => $payment
    ];

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

        event(new LicenseRequestApproved($licenseRequest));

        $student = $licenseRequest->student;
        $user = $student->user;

        if ($user && $user->fcm_token) {
            $this->firebaseService->sendNotification(
                $user->fcm_token,
                '✅ تمت الموافقة على طلب الرخصة',
                "تمت الموافقة على طلبك للرخصة بالكود: {$licenseRequest->license->code}"
            );
        }

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

         $paymentTransaction = $licenseRequest->paymentTransaction;
        if ($paymentTransaction) {
            $invoiceId = (int) $paymentTransaction->invoice_id;

            $refundInit = $this->paymentService->initiateRefund([
                'invoiceId' => $invoiceId
            ]);

            $refundInvoice = $refundInit['apiResponse']['json']['RefundInvoice'] ?? null;

            if ($refundInvoice) {
                $this->paymentService->confirmRefund([
                    'baseInvoice'   => $invoiceId,
                    'refundInvoice' => (int) $refundInvoice
                ]);
            }
        }

event(new LicenseRequestRejected($licenseRequest, $reason));
  $student = $licenseRequest->student;
        $user = $student->user;

        if ($user && $user->fcm_token) {
            $this->firebaseService->sendNotification(
                $user->fcm_token,
                ' ❌ تم رفض طلب الرخصة و استرداد مبلغك بالكامل ',
                "تم رفض   طلبك للرخصة بالكود: {$licenseRequest->license->code} و استرداد مبلغك بالكامل "
            );
        }
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

public function issueLicenseAfterExam(PracticalExamSchedule $schedule, string $issuedAt, string $expiresAt): bool
    {
        $licenseRequest = $schedule->licenseRequest;

        return $this->licenseRepository->updateDates($licenseRequest->id, $issuedAt, $expiresAt);
    }

}