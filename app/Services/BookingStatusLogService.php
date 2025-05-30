<?php
namespace App\Services;

use App\Repositories\Contracts\BookingStatusLogRepositoryInterface;
use App\Exports\BookingStatusLogsExport;

class BookingStatusLogService 
{
    protected $statusLogRepo;

    public function __construct(BookingStatusLogRepositoryInterface $statusLogRepo)
    {
        $this->statusLogRepo = $statusLogRepo;
    }

   public function getPaginatedStatusLogs(int $perPage = 10)
{
    return $this->statusLogRepo->paginateStatusLogs($perPage);
}

   public function exportBookingStatusLogs()
{
    return $this->statusLogRepo->exportBookingStatusLogs();
}

public function exportBookingStatusLogsPdf()
{
    return $this->statusLogRepo->exportBookingStatusLogsPdf();
}

}
