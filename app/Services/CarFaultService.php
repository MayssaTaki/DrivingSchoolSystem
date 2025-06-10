<?php

namespace App\Services;

use App\Models\Car;
use App\Repositories\Contracts\CarFaultRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;


class CarFaultService
{
    protected $repo;
    protected ActivityLoggerService $activityLogger;
    protected LogService $logService;

    public function __construct(
        CarFaultRepositoryInterface $repo,
        ActivityLoggerService $activityLogger,
        LogService $logService
    ) {
        $this->repo = $repo;
        $this->activityLogger = $activityLogger;
        $this->logService = $logService;
    }

    public function submitFault(array $data)
    {
        try {
            $fault = $this->repo->create($data);

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
}

