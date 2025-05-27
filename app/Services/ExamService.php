<?php
namespace App\Services;
use App\Services\TransactionService;

use App\Repositories\Contracts\ExamRepositoryInterface;

class ExamService
{
    protected $examRepo;
   protected ActivityLoggerService $activityLogger;
    protected LogService $logService;
    public function __construct(ExamRepositoryInterface $examRepo,
    ActivityLoggerService $activityLogger,
        LogService $logService,        TransactionService $transactionService,
)
    {
        $this->examRepo = $examRepo;
          $this->activityLogger = $activityLogger;
        $this->logService = $logService;
                $this->transactionService = $transactionService;

    }




public function createExamWithQuestions(array $data)
{
    try {
        return $this->transactionService->run(function () use ($data) {
            $exam = $this->examRepo->createExamWithQuestions($data);

            $this->activityLogger->log(
                'تم إنشاء امتحان جديد',
                [
                    'title' => $exam->title,
                    'duration' => $exam->duration_minutes,
                    'trainer_id' => $exam->trainer_id,
                ],
                'exams',
                $exam,
                auth()->user(),
                'created'
            );

            return $exam;
        });
    } catch (\Exception $e) {
        $this->logService->log(
            'error',
            'فشل إنشاء الامتحان',
            [
                'message' => $e->getMessage(),
                'trainer_id' => $data['trainer_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ],
            'exams'
        );

        throw $e;
    }
}

    public function listExams()
    {
        return $this->examRepo->getAllExams();
    }

    public function showExam($id)
    {
        return $this->examRepo->getExamWithQuestions($id);
    }

    public function processSubmission($examId, $answers)
    {
        return $this->examRepo->submitExam($examId, $answers);
    }

    public function startExam(int $examId, int $studentId)
{
    return $this->examRepo->startExamAttempt($examId, $studentId);
}
public function submitExam(int $attemptId, array $answers): array
{
    return $this->examRepo->submitExamAttempt($attemptId, $answers);
}

}
