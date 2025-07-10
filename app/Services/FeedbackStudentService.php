<?php
namespace App\Services;

use App\Repositories\Contracts\FeedbackStudentRepositoryInterface;
use App\Exceptions\BookingNotCompletedException;
use App\Exceptions\BookingAlreadyFeedbackException;
use App\Models\Feedback_student;
use App\Models\Booking;
use App\Services\Interfaces\FeedbackStudentServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Models\User;
use App\Events\FeedbackGiven;

class FeedbackStudentService implements FeedbackStudentServiceInterface
{
    protected ActivityLoggerServiceInterface $activityLogger;
protected FirebaseServiceInterface $firebaseservice;

   public function __construct(
    FeedbackStudentRepositoryInterface $feedbackRepo,
    ActivityLoggerServiceInterface $activityLogger,
    LogServiceInterface $logService,
    FirebaseService $firebaseService
) {
    $this->feedbackRepo = $feedbackRepo;
    $this->activityLogger = $activityLogger;
    $this->logService = $logService;
            $this->firebaseService = $firebaseService;

}


public function giveFeedback(array $data): Feedback_student
{
    try {
        $booking = Booking::with(['student.user', 'trainer', 'session'])->findOrFail($data['booking_id']);

        if ($booking->status !== 'completed') {
            throw new BookingNotCompletedException();
        }

        if ($booking->feedback) {
            throw new BookingAlreadyFeedbackException();
        }

        $feedback = $this->feedbackRepo->create([
            'booking_id' => $booking->id,
            'level' => $data['level'],
            'notes' => $data['notes'] ?? null,
        ]);

        event(new FeedbackGiven($feedback));

        $student = $booking->student;
        $user = $student->user;

        if ($user && $user->fcm_token) {
            $this->firebaseService->sendNotification(
                $user->fcm_token,
                '📝 تقييم جديد من المدرب',
                "تمت إضافة تقييم جديد لك بعد الجلسة. المستوى: {$feedback->level}" . ($feedback->notes ? "، ملاحظات: {$feedback->notes}" : '')
            );
        }

        $this->activityLogger->log(
            'تقييم طالب بعد جلسة تدريب',
            [
                'student_id' => $booking->student_id,
                'trainer_id' => $booking->trainer_id,
                'session_id' => $booking->session_id,
                'level' => $data['level'],
            ],
            'feedback_students',
            $feedback,
            auth()->user(),
            'create'
        );

        return $feedback;
    } catch (\Throwable $e) {
        $this->logService->log('error', 'فشل في إنشاء تقييم الطالب', [
            'booking_id' => $data['booking_id'] ?? null,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 'feedback_students');

        throw $e;
    }
}



   public function getStudentFeedbacks(int $studentId)
    {
        return $this->feedbackRepo->getFeedbacksByStudentId($studentId);
    }
    public function getTrainerFeedbacks(int $trainerId)
{
    return $this->feedbackRepo->getFeedbacksByTrainerId($trainerId);
}
public function getAllFeedbacksPaginated(int $perPage = 10)
{
    return $this->feedbackRepo->getAllWithPagination($perPage);
}

}

