<?php
namespace App\Repositories;
use DB;
use App\Models\TrainingSession;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;

class TrainingSessionRepository implements TrainingSessionRepositoryInterface
{
    public function create(array $data)
    {
        return TrainingSession::create($data);
    }
        public function getByTrainer(int $trainerId)
    {
        return TrainingSession::where('trainer_id', $trainerId)
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->orderBy('end_time')
            ->get();
    }
public function existsForDateAndTime(int $trainerId, string $date, string $startTime): bool
{
    return \DB::table('training_sessions')
        ->where('trainer_id', $trainerId)
        ->where('session_date', $date)
        ->where('start_time', $startTime)
        ->exists();
}
 public function cancelSessionsForDate(int $trainerId, string $date): int
    {
        return TrainingSession::where('trainer_id', $trainerId)
            ->whereDate('session_date', $date)
            ->update(['status' => 'cancelled']);
    }




}
