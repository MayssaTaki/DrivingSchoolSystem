<?php
namespace App\Services;

use App\Services\Interfaces\PracticalExamServiceInterface;
use App\Repositories\Contracts\PracticalExamRepositoryInterface;

use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\EmailVerificationServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\PracticalExamSchedule;
use App\Models\LicenseRequest;

use App\Services\Interfaces\TransactionServiceInterface;
use Illuminate\Validation\ValidationException;

class PracticalExamService implements PracticalExamServiceInterface
{
 protected PracticalExamRepositoryInterface $practRepo;
protected LogServiceInterface $logService;
protected TransactionServiceInterface $transactionService;
protected ActivityLoggerServiceInterface $activityLogger;
     protected EmailVerificationServiceInterface $emailservice;

public function __construct(PracticalExamRepositoryInterface $practRepo,LogServiceInterface $logService
        ,        ActivityLoggerServiceInterface $activityLogger,
                TransactionServiceInterface $transactionService,
                                EmailVerificationServiceInterface $emailService,

)
    {
        $this->practRepo = $practRepo;
         $this->logService = $logService;
                         $this->emailService=$emailService;

        $this->activityLogger = $activityLogger;
                $this->transactionService = $transactionService;

    }

protected function ensureLicenseRequestIsApproved($license_request)
{
    if ($license_request->status !== 'approved') {
        throw ValidationException::withMessages([
            'license_request' => 'الطلب غير مقبول.',
        ]);
    }
}

 public function scheduleExam(array $data): PracticalExamSchedule
{
    return $this->transactionService->run(function () use ($data) {

        $licenseRequest = LicenseRequest::with('student.user')->findOrFail($data['license_request_id']);

        $existing = $this->practRepo->findByLicenseRequest($licenseRequest->id);
        if ($existing) {
            throw ValidationException::withMessages([
                'license_request_id' => 'تم جدولة امتحان عملي مسبقًا لهذا الطلب.',
            ]);
        }

        $this->ensureLicenseRequestIsApproved($licenseRequest);

        $schedule = $this->practRepo->create([
            'license_request_id' => $licenseRequest->id,
            'employee_id' => auth()->user()->employee->id,
            'exam_date' => $data['exam_date'],
            'exam_time' => $data['exam_time'],
        ]);

        $this->activityLogger->log(
            'تم جدولة امتحان عملي',
            ['schedule_id' => $schedule->id],
            'practical_exam_schedules',
            $schedule,
            auth()->user(),
            'schedule_exam'
        );

        $user = $licenseRequest->student->user;
        $subject = '📅 تم تحديد موعد الامتحان العملي';
        $htmlContent = "
            <h2>مرحبا {$user->name},</h2>
            <p>تم جدولة موعد الامتحان العملي الخاص بك.</p>
            <ul>
                <li><strong>التاريخ:</strong> {$data['exam_date']}</li>
                <li><strong>الوقت:</strong> {$data['exam_time']}</li>
            </ul>
            <p>نتمنى لك التوفيق!</p>
            <p>فريق Qyada School</p>
        ";

        $this->emailService->sendCustomEmail($user, $subject, $htmlContent);

        return $schedule;

    }, function (Throwable $e) use ($data) {
        $this->logService->log('error', 'فشل جدولة امتحان عملي', [
            'data' => $data,
            'message' => $e->getMessage()
        ], 'practical_exam_schedules');

        throw $e;
    });
}

public function listAll(int $perPage = 10): LengthAwarePaginator
    {
        return $this->practRepo->paginateLatest($perPage);
    }

     public function getMySchedules(int $perPage = 10):LengthAwarePaginator
    {
        $studentId = auth()->user()->student->id;
        return $this->practRepo->getStudentSchedules($studentId, $perPage);
    }
}