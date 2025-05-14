<?php

namespace App\Services;

use App\Models\TrainingSchedule;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\TrainingSchedulesRepositoryInterface;
use App\Services\TransactionService;
use App\Exceptions\TrainingScheduleException;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;

class TrainingSchedulesService
{
    use LogsActivity;

    protected ActivityLoggerService $activityLogger;
    protected LogService $logService;

    public function __construct(
        TrainingSchedulesRepositoryInterface $trainingRepository,
        TransactionService $transactionService,
        ActivityLoggerService $activityLogger,
        LogService $logService
    ) {
        $this->trainingRepository = $trainingRepository;
        $this->transactionService = $transactionService;
        $this->activityLogger = $activityLogger;
        $this->logService = $logService;
    }

    public function getTrainerSchedules($trainerId)
    {
        try {
            return $this->trainingRepository->getByTrainer($trainerId);
        } catch (\Exception $e) {
            $this->logService->log('error', 'خطأ في استرجاع جدول التدريب', [
                'message' => $e->getMessage(),
                'trainer_id' => $trainerId,
                'trace' => $e->getTraceAsString()
            ], 'training_schedule');
            throw new \Exception('حدث خطأ أثناء استرجاع جدول المدرب', 500);
        }
    }

    public function clearTrainingCache(): void
    {
        $this->trainingRepository->clearCache();
    }

    public function createMany(array $schedules)
    {
        $created = [];

        foreach ($schedules as $data) {
            $exists = TrainingSchedule::where('trainer_id', $data['trainer_id'])
                ->where('day_of_week', $data['day_of_week'])
                ->where('start_time', $data['start_time'])
                ->exists();

            if ($exists) {
                throw new TrainingScheduleException("المدرب لديه جدول في نفس اليوم والوقت بالفعل.", 422);
            }

            $createdSchedule = $this->trainingRepository->create($data);
            $created[] = $createdSchedule;

            $this->activityLogger->log(
                'إضافة جدول تدريب',
                ['day' => $data['day_of_week'], 'start' => $data['start_time']],
                'training_schedules',
                $createdSchedule,
                auth()->user(),
                'created'
            );
        }

        $this->clearTrainingCache();
        return $created;
    }

    public function updateMany(array $schedules)
    {
        $updated = [];

        foreach ($schedules as $data) {
            $schedule = TrainingSchedule::findOrFail($data['id']);
            $trainerId = auth()->user()->trainer->id;

            if ($schedule->trainer_id !== $trainerId) {
                throw new TrainingScheduleException("غير مسموح لك بالتعديل ", 403);
            }

            $exists = TrainingSchedule::where('trainer_id', $trainerId)
                ->where('day_of_week', $data['day_of_week'])
                ->where('start_time', $data['start_time'])
                ->where('id', '!=', $data['id'])
                ->exists();

            if ($exists) {
                throw new TrainingScheduleException("يوجد جدول آخر بنفس الوقت.", 422);
            }

            $updatedSchedule = $this->trainingRepository->update($data['id'], $data);
            $updated[] = $updatedSchedule;

            $this->activityLogger->log(
                'تعديل جدول تدريب',
                ['day' => $data['day_of_week'], 'start' => $data['start_time']],
                'training_schedules',
                $updatedSchedule,
                auth()->user(),
                'updated'
            );
        }

        $this->clearTrainingCache();
        return $updated;
    }

    public function activate(int $id)
    {
        return $this->changeStatusWithCheck($id, 'active');
    }

    public function deactivate(int $id)
    {
        return $this->changeStatusWithCheck($id, 'inactive');
    }

    protected function changeStatusWithCheck(int $id, string $status)
    {
        $schedule = TrainingSchedule::findOrFail($id);

        if (auth()->user()->role !== 'employee') {
            throw new TrainingScheduleException('ليس لديك الصلاحية ', 403);
        }

        $updatedSchedule = $this->trainingRepository->changeStatus($id, $status);

        $this->activityLogger->log(
            "تغيير حالة الجدول إلى {$status}",
            ['day' => $schedule->day_of_week, 'start' => $schedule->start_time],
            'training_schedules',
            $updatedSchedule,
            auth()->user(),
            'status_changed'
        );

        $this->clearTrainingCache();

        return $updatedSchedule;
    }
}
