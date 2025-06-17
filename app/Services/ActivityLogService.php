<?php
namespace App\Services;
use App\Services\Interfaces\ActivityLogServiceInterface;

use App\Repositories\ActivityLogRepository;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;

use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService implements ActivityLogServiceInterface
{
    protected ActivityLogRepository $repository;

    public function __construct(ActivityLogRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginatedLogs(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getLogs($filters, $perPage);
    }
}
