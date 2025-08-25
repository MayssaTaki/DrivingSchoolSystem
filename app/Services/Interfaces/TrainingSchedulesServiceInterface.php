<?php

namespace App\Services\Interfaces;

interface TrainingSchedulesServiceInterface
{
    public function getTrainerSchedules($trainerId);

    public function clearTrainingCache($trainerId): void;
    public function updateFee(int $scheduleId, int $fee);

    public function createMany(array $schedules);

    public function activate(int $id);

    public function deactivate(int $id);
}
