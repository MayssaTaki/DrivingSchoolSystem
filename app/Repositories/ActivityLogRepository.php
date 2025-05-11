<?php
namespace App\Repositories;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;

class ActivityLogRepository  implements ActivityLogRepositoryInterface
{
    public function getLogs(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        if (!empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        return $query->paginate($perPage);
    }
}
