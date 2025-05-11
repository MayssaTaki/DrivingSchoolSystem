<?php
namespace App\Repositories;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use App\Models\LogEntry;
use App\Repositories\Contracts\LogRepositoryInterface;

class LogRepository implements LogRepositoryInterface
{
    public function store(array $data): void
    {
        LogEntry::create($data);
    }

    public function getAllPaginated(int $perPage = 10, ?string $level = null, ?string $channel = null): LengthAwarePaginator
    {
        $query = LogEntry::query();
    
        if ($level) {
            $query->where('level', $level);
        }
    
        if ($channel) {
            $query->where('channel', $channel);
        }
    
        return $query->latest()->paginate($perPage);
    }
}
