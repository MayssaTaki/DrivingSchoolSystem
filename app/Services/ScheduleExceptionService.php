<?php
namespace App\Services;

use App\Repositories\Interfaces\ScheduleExceptionRepositoryInterface;
use App\Exceptions\ScheduleExceptionConflictException;
use App\Exceptions\ScheduleExceptionNotFoundException;

class ScheduleExceptionService
{
    public function __construct(
        private ScheduleExceptionRepositoryInterface $repository
    ) {}

    public function getTrainerExceptions(int $trainerId)
    {
        return $this->repository->getAllForTrainer($trainerId);
    }

    public function getException(int $id)
    {
        $exception = $this->repository->getById($id);
        
        if (!$exception) {
            throw new ScheduleExceptionNotFoundException();
        }

        return $exception;
    }

    public function createException(array $data)
    {
        if ($this->repository->checkDateConflict($data['trainer_id'], $data['exception_date'])) {
            throw new ScheduleExceptionConflictException();
        }

        return $this->repository->create($data);
    }

 
    
}