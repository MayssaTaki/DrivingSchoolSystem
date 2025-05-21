<?php

namespace App\Services;
use App\Events\TrainingScheduleCreated;
use App\Models\TrainingSchedule;
use App\Repositories\Contracts\TrainingSchedulesRepositoryInterface;
use App\Services\TransactionService;
use App\Events\TrainingScheduleUpdated;
use App\Events\ScheduleNeedsSessionGeneration;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;

use App\Exceptions\TrainingScheduleException;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Log;

class TrainingSchedulesService
{
    use LogsActivity;

    protected ActivityLoggerService $activityLogger;
    protected LogService $logService;
    protected TrainingSchedulesRepositoryInterface $trainingRepository;
    protected TransactionService $transactionService;

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

    public function clearTrainingCache($trainerId): void
    {
        $this->trainingRepository->clearCache($trainerId);
    }

    protected function checkTrainerApproval($trainer)
{
    if ($trainer->status !== 'approved') {
        throw new TrainingScheduleException("لا يمكن إنشاء جدول لأن حالة حسابك غير معتمدة.", 403);
    }
}

protected function checkScheduleConflict(array $data)
{
    if ($this->trainingRepository->scheduleExists($data)) {
        throw new TrainingScheduleException("المدرب لديه جدول في نفس اليوم والوقت بالفعل.", 422);
    }
}

public function createMany(array $schedules)
{
    $trainer = auth()->user()->trainer;

    try {
        return $this->transactionService->run(function () use ($schedules, $trainer) {
            $this->checkTrainerApproval($trainer);

            $created = [];

            foreach ($schedules as $data) {
                $this->checkScheduleConflict($data);

                $createdSchedule = $this->trainingRepository->create($data);
                $created[] = $createdSchedule;


                $this->activityLogger->log(
                    'إضافة جدول تدريب',
                    ['day' => $data['day_of_week'], 'start' => $data['start_time']],
                    'training_schedules',
                    $createdSchedule,
                    auth()->user(),
                    'created schedule training'
                );
            }

            $this->clearTrainingCache($trainer->id);

          

            return TrainingSchedule::whereIn('id', collect($created)->pluck('id'))->get();
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في إنشاء جداول التدريب', [
            'message' => $e->getMessage(),
            'input' => $schedules,
            'trace' => $e->getTraceAsString(),
        ], 'training_schedules');

        throw $e;
    }
}


 

    public function activate(int $id)
{
    try {
        return $this->transactionService->run(function () use ($id) {
            $schedule = $this->trainingRepository->findById($id);

  if (Gate::denies('active', $schedule)) {
                throw new AuthorizationException('ليس لديك صلاحية تفعيل جدول التدريب.');
            }
            $updatedSchedule = $this->changeStatusWithCheck($id, 'active');
                event(new TrainingScheduleCreated($updatedSchedule));

            $this->clearTrainingCache($schedule->trainer_id);

            $this->activityLogger->log(
                'تفعيل جدول تدريب',
                ['day' => $schedule->day_of_week, 'start' => $schedule->start_time],
                'training_schedules',
                $schedule,
                auth()->user(),
                'activate  '
            );

            return $updatedSchedule;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في تفعيل الجدول التدريبي', [
            'message' => $e->getMessage(),
            'schedule_id' => $id,
            'trace' => $e->getTraceAsString(),
        ], 'training_schedules');

        throw $e;
    }
}


    public function deactivate(int $id)
{
    try {
        return $this->transactionService->run(function () use ($id) {
            $schedule = $this->trainingRepository->findById($id);
 if (Gate::denies('diactive', $schedule)) {
                throw new AuthorizationException('ليس لديك صلاحية عدم تفعيل جدول التدريب.');
            }
            $updatedSchedule = $this->changeStatusWithCheck($id, 'inactive');
            $this->clearTrainingCache($schedule->trainer_id);

            $this->activityLogger->log(
                'تعطيل جدول تدريب',
                ['day' => $schedule->day_of_week, 'start' => $schedule->start_time],
                'training_schedules',
                $schedule,
                auth()->user(),
                'deactivate '
            );

            return $updatedSchedule;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في تعطيل الجدول التدريبي', [
            'message' => $e->getMessage(),
            'schedule_id' => $id,
            'trace' => $e->getTraceAsString(),
        ], 'training_schedules');

        throw $e;
    }
}


    protected function changeStatusWithCheck(int $id, string $status)
    {
            $schedule = $this->trainingRepository->findById($id);

        $updatedSchedule = $this->trainingRepository->changeStatus($id, $status);

        $this->activityLogger->log(
            "تغيير حالة الجدول إلى {$status}",
            ['day' => $schedule->day_of_week, 'start' => $schedule->start_time],
            'training_schedules',
            $updatedSchedule,
            auth()->user(),
            'status_changed'
        );

        $trainerId = $schedule->trainer_id;
        $this->clearTrainingCache($trainerId);

        return $updatedSchedule;
    }
}
