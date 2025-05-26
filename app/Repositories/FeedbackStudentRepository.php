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

}