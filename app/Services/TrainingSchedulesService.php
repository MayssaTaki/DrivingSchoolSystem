<?php

namespace App\Services;
use App\Events\TrainingScheduleCreated;
use App\Models\TrainingSchedule;
use App\Repositories\Contracts\TrainingSchedulesRepositoryInterface;
use App\Services\TransactionService;
use App\Events\TrainingScheduleUpdated;
use App\Events\ScheduleNeedsSessionGeneration;
use App\Events\TrainingSchedulesCreated;
use App\Events\TrainingScheduleActivated;
use App\Events\TrainingScheduleDeactivated;
use Symfony\Component\HttpKernel\Exception\HttpException;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Exceptions\TrainingScheduleException;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\TrainingSchedulesServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\LogServiceInterface;


class TrainingSchedulesService implements TrainingSchedulesServiceInterface
{
    use LogsActivity;

    protected ActivityLoggerServiceInterface $activityLogger;
    protected LogServiceInterface $logService;
    protected TrainingSchedulesRepositoryInterface $trainingRepository;
    protected TransactionServiceInterface $transactionService;
protected FirebaseServiceInterface $firebaseservice;

    public function __construct(
        TrainingSchedulesRepositoryInterface $trainingRepository,
        TransactionServiceInterface $transactionService,
        ActivityLoggerServiceInterface $activityLogger,
        LogServiceInterface $logService,
        FirebaseService $firebaseService

    ) {
        $this->trainingRepository = $trainingRepository;
        $this->transactionService = $transactionService;
        $this->activityLogger = $activityLogger;
        $this->logService = $logService;
        $this->firebaseService = $firebaseService;

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

$count = count($created);

event(new TrainingSchedulesCreated($trainer, $count));
  $users = User::whereIn('role', ['employee', 'admin'])
                ->whereNotNull('fcm_token')
                ->get();

            foreach ($users as $user) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    '📅 جداول تدريب جديدة',
                    "{$trainer->first_name} {$trainer->last_name} أضاف {$count} جدول تدريب جديد.",
                );
            }
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
  if (is_null($schedule->registration_fee)) {
    throw new HttpException(422, 'يجب تحديد سعر التسجيل للجدول قبل التفعيل.');
}
            $updatedSchedule = $this->changeStatusWithCheck($id, 'active');

            event(new TrainingScheduleCreated($updatedSchedule));
            event(new TrainingScheduleActivated($updatedSchedule));

            $trainer = $updatedSchedule->trainer;
            $user = $trainer->user;

            if ($user && $user->fcm_token) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    '✅ تم تفعيل جدول التدريب',
                    "تم تفعيل جدول التدريب ليوم {$updatedSchedule->day_of_week} من {$updatedSchedule->start_time} حتى {$updatedSchedule->end_time}."
                );
            }

            $this->clearTrainingCache($updatedSchedule->trainer_id);

            $this->activityLogger->log(
                'تفعيل جدول تدريب',
                ['day' => $updatedSchedule->day_of_week, 'start' => $updatedSchedule->start_time],
                'training_schedules',
                $updatedSchedule,
                auth()->user(),
                'activate'
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
           event(new TrainingScheduleDeactivated($updatedSchedule));
$trainer = $updatedSchedule->trainer;
            $user = $trainer->user;

            if ($user && $user->fcm_token) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    '⚠️ تم تعطيل جدول التدريب',
                "تم تعطيل جدول التدريب الخاص بك ليوم {$updatedSchedule->day_of_week} من {$updatedSchedule->start_time} حتى {$updatedSchedule->end_time}.",
                );
            }
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

   public function updateFee(int $scheduleId, int $fee) 
{
    $schedule = $this->trainingRepository->findById($scheduleId);

    if (Gate::denies('Fee', $schedule)) {
        throw new AuthorizationException('ليس لديك صلاحية بتحديد السعر.');
    }

    return $this->trainingRepository->setRegistrationFee($scheduleId, $fee);
}

}
