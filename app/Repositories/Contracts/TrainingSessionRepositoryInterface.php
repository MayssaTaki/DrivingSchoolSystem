<?php
namespace App\Repositories\Contracts;

interface TrainingSessionRepositoryInterface
{
    public function create(array $data);
        public function getByTrainer(int $trainerId);
public function existsForDateAndTime(int $trainerId, string $date, string $startTime): bool;

}
