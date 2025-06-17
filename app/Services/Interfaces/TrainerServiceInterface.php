<?php

namespace App\Services\Interfaces;

use App\Models\Trainer;

interface TrainerServiceInterface
{
    public function register(array $data): Trainer;

    public function getAllTrainers(?string $name);

    public function getAllTrainersApprove(?string $name);

    public function delete(int $id): void;

    public function update(Trainer $trainer, array $data): Trainer;

    public function clearTrainerCache(): void;

    public function countTrainers(): int;

    public function approveTrainer($id): Trainer;

    public function rejectTrainer($id);

    public function getApprovedTrainers();

    public function getRejectedTrainers();

    public function getPendingTrainers();
}
