<?php
namespace App\Repositories\Contracts;
use App\Models\Trainer;

interface TrainingSchedulesRepositoryInterface
{
   public function getByTrainer($trainerId);
       public function clearCache($trainerId);

}