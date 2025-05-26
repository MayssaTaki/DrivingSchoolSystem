<?php

namespace App\Services;
use App\Repositories\Contracts\TrainerReviewRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class TrainerReviewService
{
    protected $repo;
    protected ActivityLoggerService $activityLogger;

    public function __construct(TrainerReviewRepositoryInterface $repo,
    ActivityLoggerService $activityLogger,        TransactionService $transactionService,

     protected LogService $logService)
    {        $this->activityLogger = $activityLogger;
        $this->transactionService = $transactionService;

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

            $this->activityLogger->log(
                'تم تقييم المدرب',
                ['rating' => $data['rating']],
                'trainer_reviews',
                $review,
                auth()->user(),
                'rating'
            );

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
    {   try { 

if (!auth()->user()->role === 'employee') {
    throw new AuthorizationException('ليس لديك صلاحية الموافقة على التقييم.');
}
       $approve=  $this->repo->approve($id);
         $this->activityLogger->log(
                    'تم قبول التقييم',
                      ['rating' => $approve->rating],
                    'rating',
                    $approve, 
                    auth()->user(),
                    'rating'
                );
       return $approve; }catch (\Exception $e) {
          $this->logService->log('error', 'فشل قبول  التقييم ', [
                'message' => $e->getMessage(),
            ], 'trainer_reviews');

        throw new \Exception('فشل قبول التقييم : ' . $e->getMessage());
    }
    }

     public function RejectReview($id)
    {   try { 

if (!auth()->user()->role === 'employee') {
    throw new AuthorizationException('ليس لديك صلاحية الموافقة على التقييم.');
}
       $approve=  $this->repo->reject($id);
         $this->activityLogger->log(
                    'تم رفض التقييم',
                      ['rating' => $approve->rating],
                    'rating',
                    $approve, 
                    auth()->user(),
                    'rating'
                );
       return $approve; }catch (\Exception $e) {
          $this->logService->log('error', 'فشل رفض  التقييم ', [
                'message' => $e->getMessage(),
            ], 'trainer_reviews');

        throw new \Exception('فشل رفض التقييم : ' . $e->getMessage());
    }
    }
      public function getTrainerReviews(int $trainerId): LengthAwarePaginator
    {
        return $this->repo->getByTrainerId($trainerId);
    }
}
