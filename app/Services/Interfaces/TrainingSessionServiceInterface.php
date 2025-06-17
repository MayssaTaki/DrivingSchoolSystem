<?php

namespace App\Services\Interfaces;

use App\Models\TrainingSchedule;

interface TrainingSessionServiceInterface
{
    public function generateSessionsForSchedule(TrainingSchedule $schedule): void;

    public function getRecommendedSessions(
        int $studentId,
        string $preferredDate,
        string $preferredTime,
        ?string $trainingType = null,
        int $limit = 10
    );

    public function getSessionCounts(int $trainerId, ?string $month = null): array;

    public function getTrainerSessionsGroupedByDate(int $trainerId);

    public function getScheduleSessionsGroupedByDate(int $scheduleId);
}
