<?php

namespace App\Services;
use App\Repositories\Contracts\TrainerReviewRepositoryInterface;

class TrainerReviewService
{
    protected $repo;
    protected ActivityLoggerService $activityLogger;

    public function __construct(TrainerReviewRepositoryInterface $repo,
    ActivityLoggerService $activityLogger)
    {        $this->activityLogger = $activityLogger;

        $this->repo = $repo;
    }

    public function submitReview(array $data)
    {
       try {  
      $submit=  $this->repo->create($data);
          $this->activityLogger->log(
                    'تم تقييم المدرب',
                    ['rating' => $data['rating']],
                    'rating',
                    $submit, 
                    auth()->user(),
                    'rating'
                );
                return $submit;}catch (\Exception $e) {
        throw new \Exception('فشل تقييم المدرب: ' . $e->getMessage());
    }
    }

    public function listPending()
    {
        return $this->repo->getPending();
    }

    public function approveReview($id)
    {   try { 
       $approve=  $this->repo->approve($id);
         $this->activityLogger->log(
                    'تم قبول التقييم',
                    ['rating' => $data['rating']],
                    'rating',
                    $approve, 
                    auth()->user(),
                    'rating'
                );
       return $approve; }catch (\Exception $e) {
        throw new \Exception('فشل قبول التقييم : ' . $e->getMessage());
    }
    }

    public function rejectReview($id)
    {
        return $this->repo->reject($id);
    }
}
