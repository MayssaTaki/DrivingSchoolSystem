<?php

namespace App\Repositories;
use Illuminate\Support\Facades\Cache;
use App\Models\TrainerReview;
use App\Models\Booking;

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
        return TrainerReview::where('id', $id)->update(['status' => 'approved']);
    }

    public function reject($id)
    {
        return TrainerReview::where('id', $id)->update(['status' => 'rejected']);
    }
}
