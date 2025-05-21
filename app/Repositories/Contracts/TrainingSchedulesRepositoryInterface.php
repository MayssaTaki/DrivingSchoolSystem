<?php
namespace App\Repositories\Contracts;
use App\Models\Trainer;
use App\Models\TrainingSchedule;

interface TrainingSchedulesRepositoryInterface
{
   public function getByTrainer($trainerId);
       public function clearCache($trainerId);
           public function create(array $data);
public function changeStatus(int $id, string $status);
    public function findById(int $id): ?TrainingSchedule;
   public function scheduleExists(array $criteria): bool;


}