<?php
namespace App\Services;

use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Services\ExamServiceInterface;
use App\Models\Student;
class CertificateService
{
    protected CertificateRepositoryInterface $certificateRepo;
    protected ExamService $Service;
    protected TransactionService $transactionService;
protected ActivityLoggerService $activityLogger;
    protected LogService $logService;

    public function __construct(
        CertificateRepositoryInterface $certificateRepo,
        ExamService $Service,
         TransactionService $transactionService,
        ActivityLoggerService $activityLogger,
        LogService $logService
    ) {
        $this->certificateRepo = $certificateRepo;
        $this->Service = $Service;
          $this->transactionService = $transactionService;
        $this->activityLogger = $activityLogger;
        $this->logService = $logService;
    }

   public function generateOrGetCertificate(int $studentId): ?string
{
    try {
        return $this->transactionService->run(function () use ($studentId) {
            $evaluation = $this->Service->evaluateStudent($studentId);

            if (!$evaluation['passed_all']) {
                return null;
            }

            $certificatePath = $this->certificateRepo->generateCertificate($studentId);
$student = Student::find($studentId);

            $this->activityLogger->log(
                'إصدار شهادة اجتياز',
                ['student_id' => $studentId],
                'certificates',
    $student,        
                auth()->user(),
                'generated certificate'
            );

            return $certificatePath;
        });
    } catch (\Exception $e) {
        $this->logService->log(
            'error',
            'فشل في إصدار الشهادة',
            [
                'student_id' => $studentId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ],
            'certificates'
        );

        throw $e;
    }
}

}
