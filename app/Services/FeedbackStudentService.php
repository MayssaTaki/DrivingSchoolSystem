<?php
namespace App\Services;

use App\Repositories\Contracts\FeedbackStudentRepositoryInterface;

use App\Models\Feedback_student;
class FeedbackStudentService
{
    protected ActivityLoggerService $activityLogger;

   public function __construct(
    FeedbackStudentRepositoryInterface $feedbackRepo,
    ActivityLoggerService $activityLogger,
    LogService $logService
) {
    $this->feedbackRepo = $feedbackRepo;
    $this->activityLogger = $activityLogger;
    $this->logService = $logService;
}


public function giveFeedback(array $data): Feedback_student
{
    try {
        $previousCount = Feedback_student::where('student_id', $data['student_id'])->count();
        $data['number_session'] = $previousCount + 1;

        $feedback = $this->feedbackRepo->create($data);

          $this->activityLogger->log(
        'تقييم طالب بعد جلسة تدريب',
        [
            'student_id' => $data['student_id'],
            'trainer_id' => $data['trainer_id'],
            'session_id' => $data['session_id'],
            'rating' => $data['rating'],
        ],
        'feedback_students',
        $feedback,
        auth()->user(),
        'create'
    );
        return $feedback;

    } catch (\Throwable $e) {
        $this->logService->log('error', 'فشل في إنشاء تقييم الطالب', [
            'student_id' => $data['student_id'] ?? null,
            'trainer_id' => $data['trainer_id'] ?? null,
         
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 'feedback_students');

        throw $e;
    }
}


}

