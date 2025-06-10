<?php

namespace App\Repositories;
use Illuminate\Support\Facades\Cache;
use App\Models\TrainerReview;
use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use App\Repositories\Contracts\TrainerReviewRepositoryInterface;

class TrainerReviewRepository implements TrainerReviewRepositoryInterface
{
    public function create(array $data)
    {
        return TrainerReview::create($data);
    }


 public function existsForCompletedBooking($studentId, $trainerId): bool
    {
        return TrainerReview::where('student_id', $studentId)
            ->where('trainer_id', $trainerId)
            ->exists();
    }

    public function hasCompletedBooking($studentId, $trainerId): bool
    {
        return Booking::where('student_id', $studentId)
            ->where('trainer_id', $trainerId)
            ->where('status', 'completed')
            ->exists();
    }


    public function getPending()
    {
        return TrainerReview::where('status', 'pending')->paginate(10);
    }

   public function approve($id)
{
    $review = TrainerReview::find($id);
    $review->update(['status' => 'approved']);
    return $review;
}

    public function reject($id)
    {
  $review = TrainerReview::find($id);
    $review->update(['status' => 'rejected']);
    return $review;    }

        public function getByTrainerId(int $trainerId): LengthAwarePaginator
    {
        return TrainerReview::where('trainer_id', $trainerId)
            ->paginate(10);
    }

    public function findByStatus(string $status): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $cacheKey = "trainer_reviews_{$status}_page_{$page}";

        return Cache::tags(['trainer_reviews'])->remember($cacheKey, now()->addMinutes(10), function () use ($status) {
            return TrainerReview::where('status', $status)->paginate(10);
        });
    }
    public function clearCache()
  {
      Cache::tags(['trainer_reviews'])->flush();
  }

      public function getTopTrainers(int $limit = 5)
    {
        return TrainerReview::select('trainer_id')
            ->selectRaw('AVG(rating) as avg_rating')
            ->where('status', 'approved')
            ->groupBy('trainer_id')
            ->orderByDesc('avg_rating')
            ->with('trainer')
            ->take($limit)
            ->get();
    }

   public function getWorstTrainers(int $limit = 5, array $excludedTrainerIds = [])
{
    return TrainerReview::select('trainer_id')
        ->selectRaw('AVG(rating) as avg_rating')
        ->where('status', 'approved')
        ->whereNotIn('trainer_id', $excludedTrainerIds) 
        ->groupBy('trainer_id')
        ->orderBy('avg_rating')
        ->with('trainer')
        ->take($limit)
        ->get();
}
}
