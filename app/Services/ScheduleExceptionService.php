<?php
namespace App\Services;
use Illuminate\Support\Facades\Gate;

use App\Repositories\Contracts\ScheduleExceptionRepositoryInterface;
use App\Models\ScheduleException;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;



class ScheduleExceptionService
{
    protected $repository;
    protected ActivityLoggerService $activityLogger;

    public function __construct(ScheduleExceptionRepositoryInterface $exceptionRepo,
     protected TrainingSessionRepositoryInterface $sessionRepo,
             ActivityLoggerService $activityLogger,
)
    {
        $this->exceptionRepo = $exceptionRepo;
                $this->sessionRepo = $sessionRepo;
        $this->activityLogger = $activityLogger;


    }

   public function createExceptions(int $trainerId, array $dates, ?string $reason = null): array
    {
        $created = [];

        foreach ($dates as $date) {
            $exception = $this->exceptionRepo->create([
                'trainer_id' => $trainerId,
                'exception_date' => $date,
                'reason' => $reason,
                'status' => 'pending'

            ]);

            $created[] = $exception;
        }
         $this->activityLogger->log(
                    'تم تسجيل اجازة جديدة ',
                    ['reason' => $reason ],
                    'ُexceptions',
                   $exception,
                    auth()->user(),
                    'created exception'
                );

        return $created;
    }
    public function approveException(int $exceptionId): ?ScheduleException
{
    $exception = $this->exceptionRepo->find($exceptionId);

    if (!$exception || $exception->status !== 'pending') {
        return null;
    }
 if (!Gate::allows('approve', $exception)) {
        abort(403, 'ليس لديك صلاحية للموافقة على هذه الإجازة.');
    }
    $exception->status = 'approved';
    $exception->save();
    $this->sessionRepo->cancelSessionsForDate($exception->trainer_id, $exception->exception_date);
  $this->activityLogger->log(
                    'تم الموافقة على الاجازة ',
                    [ 'exception_id' => $exception->id],
                    'ُexceptions',
                    $exception,
                    auth()->user(),
                    'approve exception'
                );
    return $exception;
}
public function rejectException(int $exceptionId): ?ScheduleException
{
    $exception = $this->exceptionRepo->find($exceptionId);

    if (!$exception || $exception->status !== 'pending') {
        return null;
    }
 if (!Gate::allows('reject', $exception)) {
        abort(403, 'ليس لديك صلاحية لرفض هذه الإجازة.');
    }
    $exception->status = 'rejected';
    $exception->save();
     $this->activityLogger->log(
                    'تم رفض  الاجازة ',
                    ['exception_id' => $exception->idدحمكدحج ],
                    'ُexceptions',
                    $exception,
                    auth()->user(),
                    'rejecte exception'
                );
    return $exception;
}


    public function updateException(ScheduleException $exception, array $data): bool
    {
        return $this->repository->update($exception, $data);
    }

    public function deleteException(ScheduleException $exception): bool
    {
        return $this->repository->delete($exception);
    }

    public function getExceptionByTrainerAndDate(int $trainerId, string $date): ?ScheduleException
    {
        return $this->repository->findByTrainerAndDate($trainerId, $date);
    }
}
