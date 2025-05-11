<?php

namespace App\Services;

use App\Models\Trainer;
use Illuminate\Support\Facades\Hash;
use App\Traits\LogsActivity;
use App\Repositories\TrainingSchedulesRepository;
use App\Services\TransactionService;
use App\Repositories\Contracts\TrainingSchedulesRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
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
        Trainer::findOrFail($trainerId);
        
        return $this->trainingRepository->getByTrainer($trainerId);
        
    } catch (ModelNotFoundException $e) {
        throw new \Exception('المدرب غير موجود', 404);
    } catch (\Exception $e) {
        throw new \Exception('حدث خطأ أثناء استرجاع جدول المدرب', 500);
    }
}


}