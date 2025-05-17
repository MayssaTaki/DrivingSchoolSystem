<?php
namespace App\Repositories\Contracts;

interface TrainingSessionRepositoryInterface
{
    public function create(array $data);
        public function getByTrainer(int $trainerId);

}
