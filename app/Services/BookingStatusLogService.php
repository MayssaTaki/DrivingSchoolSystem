<?php
namespace App\Services;

use App\Repositories\Contracts\BookingStatusLogRepositoryInterface;

class BookingStatusLogService 
{
    protected $statusLogRepo;

    public function __construct(BookingStatusLogRepositoryInterface $statusLogRepo)
    {
        $this->statusLogRepo = $statusLogRepo;
    }

    public function getPaginatedStatusLogs(int $bookingId, int $perPage = 10)
    {
        return $this->statusLogRepo->paginateStatusLogs($bookingId, $perPage);
    }
}
