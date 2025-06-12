<?php
namespace App\Repositories\Contracts;
use App\Models\Feedback_student;


interface FeedbackStudentRepositoryInterface
{
    public function create(array $data): Feedback_student;
        public function getFeedbacksByTrainerId(int $trainerId);
        public function getAllWithPagination(int $perPage = 10);
       public function getFeedbacksByStudentId(int $studentId);
}