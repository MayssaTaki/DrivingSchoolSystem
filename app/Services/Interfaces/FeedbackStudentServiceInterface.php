<?php

namespace App\Services\Interfaces;

use App\Models\Feedback_student;

interface FeedbackStudentServiceInterface
{
    /**
     * Give feedback to a student for a completed booking.
     *
     * @param array $data
     * @return Feedback_student
     */
    public function giveFeedback(array $data): Feedback_student;

    /**
     * Get all feedbacks for a specific student.
     *
     * @param int $studentId
     * @return mixed
     */
    public function getStudentFeedbacks(int $studentId);

    /**
     * Get all feedbacks for a specific trainer.
     *
     * @param int $trainerId
     * @return mixed
     */
    public function getTrainerFeedbacks(int $trainerId);

    /**
     * Get all feedbacks with pagination.
     *
     * @param int $perPage
     * @return mixed
     */
    public function getAllFeedbacksPaginated(int $perPage = 10);
}
