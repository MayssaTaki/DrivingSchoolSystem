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
    public function getTopTrainers(int $limit = 5);
   public function getWorstTrainers(int $limit = 5, array $excludedTrainerIds = []);    public function clearCache();
    public function findByStatus(string $status): LengthAwarePaginator;
    public function getByTrainerId(int $trainerId): LengthAwarePaginator;

}
