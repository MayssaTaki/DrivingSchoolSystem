<?php
namespace App\Services;

use App\Repositories\Contracts\ScheduleExceptionRepositoryInterface;
use App\Exceptions\ScheduleExceptionConflictException;
use App\Exceptions\ScheduleExceptionNotFoundException;

class ScheduleExceptionService
{ 
    Protected ScheduleExceptionRepositoryInterface $repository;
    public function __construct(
         ScheduleExceptionRepositoryInterface $repository
    ) {
                $this->repository = $repository;

    }

    public function getTrainerExceptions(int $trainerId)
    {
        return $this->repository->getAllForTrainerPaginated($trainerId);
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