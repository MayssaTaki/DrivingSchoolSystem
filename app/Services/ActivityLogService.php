<?php
namespace App\Services;

use App\Repositories\ActivityLogRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    protected ActivityLogRepository $repository;

    public function __construct(ActivityLogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginatedLogs(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getLogs($filters, $perPage);
    }
}
