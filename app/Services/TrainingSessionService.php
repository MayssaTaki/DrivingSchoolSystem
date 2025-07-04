<?php
namespace App\Services;
use Log;
use App\Models\TrainingSchedule;
use App\Models\Car;

use App\Models\TrainingSession;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use App\Services\Interfaces\TrainingSessionServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Repositories\Contracts\CarRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TrainingSessionService implements TrainingSessionServiceInterface
{
    public function __construct( TrainingSessionRepositoryInterface $repo,
            TransactionServiceInterface $transactionService,        ActivityLoggerServiceInterface $activityLogger,
        LogServiceInterface $logService,
        CarRepositoryInterface $carRepo,

) {
        $this->repo=$repo;
                $this->carRepo=$carRepo;

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


public function getRecommendedSessions(
        int $studentId,
        string $preferredDate,
        string $preferredTime,
        ?string $trainingType = null,
        int $limit = 10
    ) {
        $preferredDateTime = Carbon::parse("$preferredDate $preferredTime");

        $query = TrainingSession::query()
            ->where('status', 'available')
            ->whereDate('session_date', '>=', $preferredDate);

    

        if ($trainingType) {
            $query->whereHas('trainer', function ($q) use ($trainingType) {
                $q->where('training_type', $trainingType);
            });
        }

        return $query->orderByRaw(
                "ABS(TIMESTAMPDIFF(SECOND, CONCAT(session_date, ' ', start_time), ?))",
                [$preferredDateTime]
            )
            ->limit($limit)
            ->get();
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

public function getScheduleSessionsGroupedByDate(int $scheduleId)
{
    $sessions = $this->repo->getBySchedule($scheduleId);

    return $sessions->groupBy('session_date')->map(function ($sessions, $date) {
        return (object)[
            'date' => $date,
            'sessions' => $sessions->sortBy('start_time')->values()
        ];
    })->values();
}



    
}
