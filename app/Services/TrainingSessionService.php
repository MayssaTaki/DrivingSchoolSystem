<?php
namespace App\Services;
use Log;
use App\Models\TrainingSchedule;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TrainingSessionService
{
    public function __construct( TrainingSessionRepositoryInterface $repo,
            TransactionService $transactionService,        ActivityLoggerService $activityLogger,
        LogService $logService,

) {
        $this->repo=$repo;
         $this->activityLogger = $activityLogger;
        $this->logService = $logService;
                $this->transactionService = $transactionService;

    }

public function generateSessionsForSchedule(TrainingSchedule $schedule): void
{
    if (!$schedule->valid_from || !$schedule->valid_to) return;

    try {
        $this->transactionService->run(function () use ($schedule) {
            $sessionsToInsert = [];

            $generateDaySessions = function ($date) use ($schedule, &$sessionsToInsert) {
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

                    if (!$this->repo->existsForDateAndTime(
                        $schedule->trainer_id,
                        $date->toDateString(),
                        $currentSlot->format('H:i')
                    )) {
                        $sessionsToInsert[] = [
                            'schedule_id' => $schedule->id,
                            'trainer_id' => $schedule->trainer_id,
                            'session_date' => $date->toDateString(),
                            'start_time' => $currentSlot->format('H:i'),
                            'end_time' => $slotEnd->format('H:i'),
                            'status' => 'available',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
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

            if (!empty($sessionsToInsert)) {
                $this->repo->Create($sessionsToInsert);

                $this->activityLogger->log(
                    'تم إنشاء جلسات تدريب مجدولة',
                    [
                        'schedule_id' => $schedule->id,
                        'trainer_id' => $schedule->trainer_id,
                        'count' => count($sessionsToInsert),
                    ],
                    'training_sessions',
                    $schedule,
                    auth()->user(),
                    'created sessions'
                );
            }
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في إنشاء الجلسات المجدولة', [
            'message' => $e->getMessage(),
            'schedule_id' => $schedule->id,
            'trace' => $e->getTraceAsString(),
        ], 'training_sessions');

        throw $e;
    }
}


 public function getSessionCounts(int $trainerId, ?string $month = null): array
    {
        return [
            'total' => $this->repo->countAllByTrainer($trainerId, $month),
            'available' => $this->repo->countByStatus($trainerId, 'available', $month),
            'booked' => $this->repo->countByStatus($trainerId, 'booked', $month),
         'completed' => $this->repo->countByStatus($trainerId, 'completed', $month),
          'vacation' => $this->repo->countByStatus($trainerId, 'vacation', $month),
            'cancelled' => $this->repo->countByStatus($trainerId, 'cancelled', $month),
        ];
    }

public function getTrainerSessionsGroupedByDate(int $trainerId)
{
    $sessions = $this->repo->getByTrainer($trainerId);

    return $sessions->groupBy('session_date')->map(function ($sessions, $date) {
        return (object)[
            'date' => $date,
            'sessions' => $sessions->sortBy('start_time')->values()
        ];
    })->values();
}


    
}
