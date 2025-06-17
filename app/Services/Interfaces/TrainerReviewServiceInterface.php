<?php

namespace App\Services\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TrainerReviewServiceInterface
{
    public function submitReview(array $data);

    public function listPending();

    public function approveReview($id);

    public function rejectReview($id);

    public function getTrainerReviews(int $trainerId): LengthAwarePaginator;

    public function getPendingReviews(): LengthAwarePaginator;

    public function getApprovedReviews(): LengthAwarePaginator;

    public function getRejectedReviews(): LengthAwarePaginator;

    public function clearReviewCache(): void;

    public function getTop5Trainers();

    public function getWorst5Trainers(array $excludedTrainerIds = []);
}
