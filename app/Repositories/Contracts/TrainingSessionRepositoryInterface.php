<?php
namespace App\Repositories\Contracts;

interface TrainingSessionRepositoryInterface
{
    public function create(array $data);
        public function getByTrainer(int $trainerId);
public function existsForDateAndTime(int $trainerId, string $date, string $startTime): bool;
 public function cancelSessionsForDate(int $trainerId, string $date): int;
public function find(int $id);
public function updateStatus(int $sessionId, string $status): bool;
public function getAvailableSessions();
 public function countAllByTrainer(int $trainerId, ?string $month = null): int;
    public function countByStatus(int $trainerId, string $status, ?string $month = null): int;
}
