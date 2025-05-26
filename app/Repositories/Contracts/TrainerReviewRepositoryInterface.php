<?php
namespace App\Repositories\Contracts;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TrainerReviewRepositoryInterface
{
    public function create(array $data);
     public function existsForCompletedBooking($studentId, $trainerId): bool;
    public function hasCompletedBooking($studentId, $trainerId): bool;
    public function getPending();
    public function approve($id);
    public function reject($id);
    public function getByTrainerId(int $trainerId): LengthAwarePaginator;

}
