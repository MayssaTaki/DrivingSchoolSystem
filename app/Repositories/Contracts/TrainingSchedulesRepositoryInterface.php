<?php
namespace App\Repositories\Contracts;
use App\Models\Trainer;

interface TrainingSchedulesRepositoryInterface
{
   public function getByTrainer($trainerId);
       public function clearCache($trainerId);
           public function create(array $data);
public function update(int $id, array $data);
public function changeStatus(int $id, string $status);



}