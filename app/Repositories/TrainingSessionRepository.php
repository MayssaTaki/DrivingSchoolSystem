<?php
namespace App\Repositories;

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

}
