<?php
namespace App\Services;

use App\Models\TrainingSchedule;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TrainingSessionService
{
    public function __construct( TrainingSessionRepositoryInterface $repo) {
        $this->repo=$repo;
    }

 public function generateSessionsForSchedule(TrainingSchedule $schedule)
{
    if (!$schedule->valid_from || !$schedule->valid_to) return;


    $generateDaySessions = function ($date) use ($schedule) {
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);

        $durationMinutes = $startTime->diffInMinutes($endTime);

        if ($durationMinutes % 60 !== 0) {
            \Log::warning("⛔ مدة غير قابلة للتقسيم: " . $schedule->start_time . " - " . $schedule->end_time);
            return;
        }

        $currentSlot = $startTime->copy();
        while ($currentSlot < $endTime) {
            $slotEnd = $currentSlot->copy()->addHour();

            if ($slotEnd > $endTime) break;

            try {
                if (!$this->repo->existsForDateAndTime(
                        $schedule->trainer_id,
                        $date->toDateString(),
                        $currentSlot->format('H:i')
                    )) {
                    $this->repo->create([
                        'schedule_id' => $schedule->id,
                        'trainer_id' => $schedule->trainer_id,
                        'session_date' => $date->toDateString(),
                        'start_time' => $currentSlot->format('H:i'),
                        'end_time' => $slotEnd->format('H:i'),
                        'status' => 'available'
                    ]);
                }
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() != 23000) throw $e;
            }

            $currentSlot = $slotEnd;
        }
    };

    if ($schedule->is_recurring) {
        $from = Carbon::parse($schedule->valid_from);
        $to = Carbon::parse($schedule->valid_to);
        $limitTo = $from->copy()->addMonth();
        $end = $to->lessThan($limitTo) ? $to : $limitTo;

        $period = CarbonPeriod::create($from, $end);

        foreach ($period as $date) {
            if (strtolower($date->englishDayOfWeek) === $schedule->day_of_week) {
                $generateDaySessions($date);
            }
        }
    } else {
        $period = CarbonPeriod::create($schedule->valid_from, $schedule->valid_to);

        foreach ($period as $date) {
            if (strtolower($date->englishDayOfWeek) === $schedule->day_of_week) {
                $generateDaySessions($date);
                break;
            }
        }
    }
}




      public function getTrainerSessions(int $trainerId)
    {
        return $this->repo->getByTrainer($trainerId);
    }
}
