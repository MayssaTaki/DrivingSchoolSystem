<?php
namespace App\Services;
use Illuminate\Support\Facades\Gate;
use App\Services\TransactionService;
use Illuminate\Support\Collection;
use App\Models\Trainer;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Events\TrainerExceptionCreated;
use App\Events\ExceptionApproved;
use App\Events\ExceptionRejected;


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
protected FirebaseServiceInterface $firebaseservice;

    public function __construct(ScheduleExceptionRepositoryInterface $exceptionRepo,
     protected TrainingSessionRepositoryInterface $sessionRepo,
             ActivityLoggerServiceInterface $activityLogger, LogServiceInterface $logService,
                     TransactionServiceInterface $transactionService,
                     TrainerRepositoryInterface $trainerrepo,
        FirebaseService $firebaseService


)
    {
        $this->trainerrepo=$trainerrepo;
                $this->logService = $logService;
        $this->exceptionRepo = $exceptionRepo;
                $this->sessionRepo = $sessionRepo;
        $this->activityLogger = $activityLogger;
        $this->transactionService = $transactionService;
        $this->firebaseService = $firebaseService;


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

            $trainer = \App\Models\Trainer::findOrFail($trainerId); 
            $count = count($created);

            event(new TrainerExceptionCreated($trainer, $count, $reason));

            $users = User::whereIn('role', ['employee', 'admin'])
                ->whereNotNull('fcm_token')
                ->get();

            foreach ($users as $user) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    '📆 طلب إجازة جديد',
                    "{$trainer->first_name} {$trainer->last_name} طلب إجازة لعدد {$count} يوم" . ($reason ? "، السبب: {$reason}" : '')
                );
            }

            $this->activityLogger->log(
                'تم تسجيل إجازة جديدة',
                ['reason' => $reason, 'count' => $count],
                'exceptions',
                $created[0],
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

            event(new ExceptionApproved($exception));

            $trainer = $exception->trainer;
            $user = $trainer->user;

            if ($user && $user->fcm_token) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    '✅ تمت الموافقة على طلب الإجازة',
                    "تمت الموافقة على إجازتك بتاريخ {$exception->exception_date}" . 
                    ($exception->reason ? "، السبب: {$exception->reason}" : '')
                );
            }

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
event(new ExceptionRejected($exception));
 $trainer = $exception->trainer;
            $user = $trainer->user;

            if ($user && $user->fcm_token) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    '❌ تم رفض طلب الإجازة',
                    "تمت رفض  إجازتك بتاريخ {$exception->exception_date}" . 
                    ($exception->reason ? "، السبب: {$exception->reason}" : '')
                );
            }
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
