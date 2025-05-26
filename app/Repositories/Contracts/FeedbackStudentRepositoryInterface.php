<?php
namespace App\Repositories\Contracts;
use App\Models\Feedback_student;

interface FeedbackStudentRepositoryInterface
{
    public function create(array $data): Feedback_student;
}