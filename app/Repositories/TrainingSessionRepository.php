<?php
namespace App\Repositories;
use DB;
use Carbon\Carbon;
use App\Models\TrainingSession;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;

class TrainingSessionRepository implements TrainingSessionRepositoryInterface
{
    public function create(array $data)
    {
        return TrainingSession::create($data);
    }
    public function find(int $id)
{
    return TrainingSession::find($id);
}
public function updateStatus(int $sessionId, string $status): bool
{
    return TrainingSession::where('id', $sessionId)
        ->update(['status' => $status]);
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

 public function countAllByTrainer(int $trainerId, ?string $month = null): int
    {
        $query = TrainingSession::where('trainer_id', $trainerId);

        if ($month) {
            $query->whereMonth('session_date', Carbon::parse($month)->month)
                  ->whereYear('session_date', Carbon::parse($month)->year);
        }

        return $query->count();
    }

    public function countByStatus(int $trainerId, string $status, ?string $month = null): int
    {
        $query = TrainingSession::where('trainer_id', $trainerId)
                                ->where('status', $status);

        if ($month) {
            $query->whereMonth('session_date', Carbon::parse($month)->month)
                  ->whereYear('session_date', Carbon::parse($month)->year);
        }

        return $query->count();
    }


}
