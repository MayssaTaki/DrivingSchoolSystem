<?php
namespace App\Services;
use Illuminate\Support\Facades\Gate;
use App\Services\TransactionService;
use Illuminate\Support\Collection;
use App\Models\Trainer;
use Illuminate\Pagination\LengthAwarePaginator;


use App\Models\User;
use App\Repositories\Contracts\ScheduleExceptionRepositoryInterface;
use App\Models\ScheduleException;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use App\Repositories\Contracts\TrainerRepositoryInterface;
use App\Services\Interfaces\ScheduleExceptionServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;



class ScheduleExceptionService implements ScheduleExceptionServiceInterface
{
    protected $repository;
    protected ActivityLoggerServiceInterface $activityLogger;
    protected LogServiceInterface $logService;
protected TrainerRepositoryInterface $trainerrepo;

    public function __construct(ScheduleExceptionRepositoryInterface $exceptionRepo,
     protected TrainingSessionRepositoryInterface $sessionRepo,
             ActivityLoggerServiceInterface $activityLogger, LogServiceInterface $logService,
                     TransactionServiceInterface $transactionService,
                     TrainerRepositoryInterface $trainerrepo,


)
    {
        $this->trainerrepo=$trainerrepo;
                $this->logService = $logService;
        $this->exceptionRepo = $exceptionRepo;
                $this->sessionRepo = $sessionRepo;
        $this->activityLogger = $activityLogger;
        $this->transactionService = $transactionService;


    }

  public function createExceptions(int $trainerId, array $dates, ?string $reason = null): array
{
    try {
        return $this->transactionService->run(function () use ($trainerId, $dates, $reason) {
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
                    'تم تسجيل اجازة جديدة',
                    ['reason' => $reason],
                    'exceptions',
                    $exception,
                    auth()->user(),
                    'created exception'
                );
            
            $this->clearExceptionCache();

            return $created;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل تسجيل الإجازة', [
            'message' => $e->getMessage(),
            'trainer_id' => $trainerId,
            'exception_id' => $exceptionId,
            'trace' => $e->getTraceAsString()
        ], 'exception');

        throw $e;
    }
}

    public function approveException(int $exceptionId): ?ScheduleException
{
    try {
        return $this->transactionService->run(function () use ($exceptionId) {
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
                'تم الموافقة على الاجازة',
                ['exception_id' => $exception->id],
                'exceptions',
                $exception,
                auth()->user(),
                'approve exception'
            );
            $this->clearExceptionCache();

            return $exception;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل الموافقة على الإجازة', [
            'message' => $e->getMessage(),
            'exception_id' => $exceptionId,
            'trace' => $e->getTraceAsString()
        ], 'exception');

        throw $e;
    }
}

public function rejectException(int $exceptionId): ?ScheduleException
{
    try {
        return $this->transactionService->run(function () use ($exceptionId) {
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
                'تم رفض الإجازة',
                ['exception_id' => $exception->id],
                'exceptions',
                $exception,
                auth()->user(),
                'rejected exception'
            );
            $this->clearExceptionCache();

            return $exception;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل رفض الإجازة', [
            'message' => $e->getMessage(),
            'exception_id' => $exceptionId,
            'trace' => $e->getTraceAsString()
        ], 'exception');

        throw $e;
    }
}

   
public function clearExceptionCache(): void
    {
        $this->exceptionRepo->clearCache();

       
    }
    public function getAllExceptionsByTrainer(int $trainerId): LengthAwarePaginator  
{
$trainer = $this->trainerrepo->find($trainerId);

    return $this->exceptionRepo->findAllByTrainer($trainerId);
}

public function getAllTrainersExceptions(): LengthAwarePaginator
{
    return $this->exceptionRepo->findAll();
} 

 public function getPendingExceptions(): LengthAwarePaginator
    {
        return $this->exceptionRepo->findByStatus('pending');
    }

    public function getApprovedExceptions(): LengthAwarePaginator
    {
        return $this->exceptionRepo->findByStatus('approved');
    }

    public function getRejectedExceptions(): LengthAwarePaginator
    {
        return $this->exceptionRepo->findByStatus('rejected');
    }
}
