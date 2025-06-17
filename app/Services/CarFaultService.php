<?php

namespace App\Services;

use App\Models\Car;
use App\Repositories\Contracts\CarFaultRepositoryInterface;
use App\Services\Interfaces\CarFaultServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\LogServiceInterface;

use App\Repositories\Contracts\CarRepositoryInterface;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;


class CarFaultService implements CarFaultServiceInterface
{
    protected $repo;
    protected ActivityLoggerServiceInterface $activityLogger;
    protected LogServiceInterface $logService;
    protected CarRepositoryInterface $carRepo;
    protected TransactionServiceInterface $transactionService;

    public function __construct(
        CarFaultRepositoryInterface $repo,
        ActivityLoggerServiceInterface $activityLogger,
        LogServiceInterface $logService,
        CarRepositoryInterface $carRepo,
        TransactionServiceInterface $transactionService,


    ) {
        $this->repo = $repo;
         $this->transactionService = $transactionService;
        $this->carRepo = $carRepo;
        $this->activityLogger = $activityLogger;
        $this->logService = $logService;
    }

    public function submitFault(array $data)
    {
        try {
            $fault = $this->repo->create($data);
        $this->clearFaultCache();

            $this->activityLogger->log(
                'تم تسجيل عطل جديد للسيارة',
                [
                    'car_id' => $data['car_id'],
                    'comment' => $data['comment'] ?? null,
                ],
                'car_faults',  
                $fault,       
                auth()->user(), 
                'fault'        
            );

            return $fault;
        } catch (\Exception $e) {
            $this->logService->log(
                'error',
                'فشل تسجيل عطل للسيارة',
                [
                    'message' => $e->getMessage(),
                    'data' => $data
                ],
                'car_faults'  
            );

            throw new \Exception('فشل تسجيل عطل السيارة: ' . $e->getMessage());
        }
    }
    public function getAllLatestFaults()
{
    return $this->repo->getAllLatest();
}
public function getFaultsByTrainer($trainerId)
{
    return $this->repo->getFaultsByTrainer($trainerId);
}
 public function clearfaultCache(): void
    {
        $this->repo->clearFaultsCache();
    }
    public function markCarAsInRepairByFault(int $faultId)
{
    try {
        return $this->transactionService->run(function () use ($faultId) {
            $fault = $this->repo->findWithLock($faultId);
            $car = $this->carRepo->findWithLock($fault->car_id);
         $this->ensureFaultIsNew($fault->id);
            $this->ensureCarIsAvailable($car->id);

            $this->carRepo->updateStatus($car->id, 'in_repair');
            $this->repo->updateStatus($fault->id, 'in_progress');
        $this->clearFaultCache();
        $this->carRepo->clearCache();

            $this->activityLogger->log(
                'تحويل السيارة إلى التصليح',
                [
                    'car_id' => $car->id,
                    'car_make' => $car->make,
                    'car_model' => $car->model,
                    'fault_id' => $fault->id,
                    'fault_comment' => $fault->comment,
                ],
                'car_faults',
                $fault,
                auth()->user(),
                'update'
            );

            return true;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في تحويل السيارة إلى وضع التصليح', [
            'fault_id' => $faultId,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 'car_faults');

        throw $e;
    }
}


protected function ensureCarIsRepair(int $carId)
{
    if (!$this->carRepo->isCarRepair($carId)) {
        throw ValidationException::withMessages([
            'car' => 'السيارة ليست بالتصليح .',
        ]);
    }
}
protected function ensureCarIsAvailable(int $carId)
{
    if (!$this->carRepo->isCarAvailable($carId)) {
        throw ValidationException::withMessages([
            'car' => 'السيارة محجوزة  غير متاحة للتصليح.',
        ]);
    }
}
protected function ensureFaultIsProgress(int $faultId)
{
    if (!$this->repo->isFaultProgress($faultId)) {
        throw ValidationException::withMessages([
            'fault' => 'السيارة ليست بالتصليح .',
        ]);
    }
}
protected function ensureFaultIsNew(int $faultId)
{
    if (!$this->repo->isFaultNew($faultId)) {
        throw ValidationException::withMessages([
            'fault' => 'السيارة ليست  معطلة .',
        ]);
    }
}

public function markCarAsResolvedByFault(int $faultId)
{
    try {
        return $this->transactionService->run(function () use ($faultId) {
            $fault = $this->repo->findWithLock($faultId);
            $car = $this->carRepo->findWithLock($fault->car_id);
         $this->ensureCarIsRepair($car->id);
         $this->ensureFaultIsProgress($fault->id);
            $this->carRepo->updateStatus($car->id, 'available');
            $this->repo->updateStatus($fault->id, 'resolved');
        $this->clearFaultCache();
        $this->carRepo->clearCache();

            $this->activityLogger->log(
                'تم تصليح السيارة',
                [
                    'car_id' => $car->id,
                    'car_make' => $car->make,
                    'car_model' => $car->model,
                    'fault_id' => $fault->id,
                    'fault_comment' => $fault->comment,
                ],
                'car_faults',
                $fault,
                auth()->user(),
                'update'
            );

            return true;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في اتمام عملية التصليح ', [
            'fault_id' => $faultId,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 'car_faults');

        throw $e;
    }
}

public function countFaultsPerCar()
{
    return $this->repo->countFaultsPerCar();
}

public function getTopFaultedCars($limit = 5)
{
    return $this->repo->getTopFaultedCars($limit);
}

public function getMonthlyFaultsCount($year = null)
{
    return $this->repo->getMonthlyFaultsCount($year);
}

public function getAverageMonthlyFaultsPerCar($year = null)
{
    return $this->repo->getAverageMonthlyFaultsPerCar($year);
}

public function getFaultsStatusCountPerCar()
{
    return $this->repo->getFaultsStatusCountPerCar();
}

}

