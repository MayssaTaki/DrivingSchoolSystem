<?php

namespace App\Services;
use App\Repositories\Contracts\TrainerReviewRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\Interfaces\TrainerReviewServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Events\TrainerReviewed;
use App\Events\ReviewApproved;
use App\Events\ReviewRejected;
use App\Models\User;
class TrainerReviewService implements TrainerReviewServiceInterface
{
    protected $repo;
    protected ActivityLoggerServiceInterface $activityLogger;
protected FirebaseServiceInterface $firebaseservice;

    public function __construct(TrainerReviewRepositoryInterface $repo,
    ActivityLoggerServiceInterface $activityLogger,        TransactionServiceInterface $transactionService,

     protected LogServiceInterface $logService
     ,
             FirebaseService $firebaseService
)
    {        $this->activityLogger = $activityLogger;
        $this->transactionService = $transactionService;
        $this->firebaseService = $firebaseService;

        $this->repo = $repo;
    }

    public function submitReview(array $data)
    {
        $studentId = $data['student_id'];
        $trainerId = $data['trainer_id'];

        if (!$this->repo->hasCompletedBooking($studentId, $trainerId)) {
            throw ValidationException::withMessages([
                'booking' => 'لا يمكنك تقييم هذا المدرب لأنك لم تكمل جلسة تدريبية معه.'
            ]);
        }

        if ($this->repo->existsForCompletedBooking($studentId, $trainerId)) {
            throw ValidationException::withMessages([
                'review' => 'لقد قمت بتقييم هذا المدرب مسبقًا.'
            ]);
        }

        try {
            $review = $this->repo->create($data);
event(new TrainerReviewed($review));
$users = User::whereIn('role', ['employee', 'admin'])
                ->whereNotNull('fcm_token')
                ->get();

            foreach ($users as $user) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                  '⭐ تقييم جديد للمدرب',
                    "تم إضافة تقييم جديد للمدرب : {$review->trainer->first_name} {$review->trainer->last_name}  بتقييم: {$review->rating}",
                );
            }
            $this->activityLogger->log(
                'تم تقييم المدرب',
                ['rating' => $data['rating']],
                'trainer_reviews',
                $review,
                auth()->user(),
                'rating'
            );
              $this->clearReviewCache();

            return $review;
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل تقييم المدرب', [
                'message' => $e->getMessage(),
                'data' => $data
            ], 'trainer_reviews');

            throw new \Exception('فشل تقييم المدرب: ' . $e->getMessage());
        }
    }
    public function listPending()
    {
        return $this->repo->getPending();
    }

  public function approveReview($id)
{
    try {
        if (auth()->user()->role !== 'employee') {
            throw new AuthorizationException('ليس لديك صلاحية الموافقة على التقييم.');
        }

        $review = $this->repo->approve($id);

        event(new ReviewApproved($review));

        $student = $review->student; 
        $user = $student->user;

        if ($user && $user->fcm_token) {
            $this->firebaseService->sendNotification(
                $user->fcm_token,
                '✅ تم قبول تقييمك',
                "تمت الموافقة على تقييمك للمدرب  بتقييم: {$review->rating} نجوم."
            );
        }

        $this->activityLogger->log(
            'تم قبول التقييم',
            ['rating' => $review->rating],
            'rating',
            $review,
            auth()->user(),
            'rating'
        );

        $this->clearReviewCache();

        return $review;
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل قبول التقييم', [
            'message' => $e->getMessage(),
        ], 'trainer_reviews');

        throw new \Exception('فشل قبول التقييم: ' . $e->getMessage());
    }
}


  public function rejectReview($id)
{
    try {
        if (auth()->user()->role !== 'employee') {
            throw new AuthorizationException('ليس لديك صلاحية رفض التقييم.');
        }

        $review = $this->repo->reject($id); // تأكد أن هذه الدالة تُرجع كائن التقييم

        event(new ReviewRejected($review));

        $student = $review->student; // تأكد أن العلاقة student موجودة في نموذج Review
        $user = $student->user;

        if ($user && $user->fcm_token) {
            $this->firebaseService->sendNotification(
                $user->fcm_token,
                '❌ تم رفض تقييمك',
                "تم رفض تقييمك للمدرب بتقييم: {$review->rating} نجوم."
            );
        }

        $this->activityLogger->log(
            'تم رفض التقييم',
            ['rating' => $review->rating],
            'rating',
            $review,
            auth()->user(),
            'reject rating'
        );

        $this->clearReviewCache();

        return $review;
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل رفض التقييم', [
            'message' => $e->getMessage(),
        ], 'trainer_reviews');

        throw new \Exception('فشل رفض التقييم: ' . $e->getMessage());
    }
}

    
      public function getTrainerReviews(int $trainerId): LengthAwarePaginator
    {
        return $this->repo->getByTrainerId($trainerId);
    }

    public function getPendingReviews(): LengthAwarePaginator
    {
        return $this->repo->findByStatus('pending');
    }

    public function getApprovedReviews(): LengthAwarePaginator
    {
        return $this->repo->findByStatus('approved');
    }

    public function getRejectedReviews(): LengthAwarePaginator
    {
        return $this->repo->findByStatus('rejected');
    }
    public function clearReviewCache(): void
    {
        $this->repo->clearCache();

    }

 public function getTop5Trainers()
{
    $trainers = $this->repo->getTopTrainers(5);

    return $trainers->map(function ($item) {
        $avg = round($item->avg_rating, 1);

        return [
            'trainer_id' => $item->trainer_id,
            'average_rating' => number_format($avg, 1),
            'trainer_name' => $item->trainer->first_name . ' ' . $item->trainer->last_name,
            'rating_text' => $this->getRatingText($avg),
        ];
    });
}

public function getWorst5Trainers(array $excludedTrainerIds = [])
{
    $trainers = $this->repo->getWorstTrainers(5, $excludedTrainerIds);

    return $trainers->map(function ($item) {
        $avg = round($item->avg_rating, 1);

        return [
            'trainer_id' => $item->trainer_id,
            'average_rating' => number_format($avg, 1),
            'trainer_name' => $item->trainer->first_name . ' ' . $item->trainer->last_name,
            'rating_text' => $this->getRatingText($avg),
        ];
    });
}


private function getRatingText(float $rating): string
{
    return match (true) {
        $rating >= 4.5 => 'ممتاز',
        $rating >= 3.5 => 'جيد جدًا',
        $rating >= 2.5 => 'جيد',
        $rating >= 1.5 => 'مقبول',
        default        => 'ضعيف',
    };
}


}
