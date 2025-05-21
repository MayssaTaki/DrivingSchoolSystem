<?php
namespace App\Repositories\Contracts;
interface TrainerReviewRepositoryInterface
{
    public function create(array $data);
     public function existsForCompletedBooking($studentId, $trainerId): bool;
    public function hasCompletedBooking($studentId, $trainerId): bool;
    public function getPending();
    public function approve($id);
    public function reject($id);
}
