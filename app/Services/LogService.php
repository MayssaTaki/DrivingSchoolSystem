<?php
namespace App\Services;
use App\Models\LogEntry;
use App\Repositories\Contracts\LogRepositoryInterface;
use Illuminate\Support\Facades\Gate;

class LogService
{
    protected LogRepositoryInterface $logRepository;

    public function __construct(LogRepositoryInterface $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    public function log(string $level, string $message, array $context = [], ?string $channel = null): void
    {
        $this->logRepository->store([
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'channel' => $channel
        ]);
    }

    public function getPaginatedLogs(int $perPage = 10, ?string $level = null, ?string $channel = null)
    {

        return $this->logRepository->getAllPaginated($perPage, $level, $channel);
    }
}
