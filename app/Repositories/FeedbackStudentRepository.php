<?php

namespace App\Repositories;

use App\Models\Feedback_student;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\FeedbackStudentRepositoryInterface;

class FeedbackStudentRepository implements FeedbackStudentRepositoryInterface
{
  public function create(array $data): Feedback_student {
    return Feedback_student::create($data);
}

   public function getFeedbacksByStudentId(int $studentId)
    {
        return Feedback_student::whereHas('booking', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->get();
    }
    public function getFeedbacksByTrainerId(int $trainerId)
{
    return Feedback_student::whereHas('booking', function ($query) use ($trainerId) {
        $query->where('trainer_id', $trainerId);
    })->get();
}
public function getAllWithPagination(int $perPage = 10)
{
    return Feedback_student::with(['booking.student.user', 'booking.trainer.user', 'booking.session'])
        ->latest()
        ->paginate($perPage);
}

}