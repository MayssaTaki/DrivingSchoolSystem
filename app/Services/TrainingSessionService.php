<?php
namespace App\Services;

use App\Models\TrainingSchedule;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TrainingSessionService
{
    public function __construct(private TrainingSessionRepositoryInterface $repo) {}

    public function generateSessionsForSchedule(TrainingSchedule $schedule)
    {
        if (!$schedule->is_recurring || !$schedule->valid_from || !$schedule->valid_to) return;

        $period = CarbonPeriod::create($schedule->valid_from, $schedule->valid_to);

        foreach ($period as $date) {
            if (strtolower($date->englishDayOfWeek) === $schedule->day_of_week) {
                $this->repo->create([
                    'schedule_id' => $schedule->id,
                    'trainer_id' => $schedule->trainer_id,
                    'session_date' => $date->toDateString(),
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'status' => 'scheduled'
                ]);
            }
        }
    }
      public function getTrainerSessions(int $trainerId)
    {
        return $this->repo->getByTrainer($trainerId);
    }
}
