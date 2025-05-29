<?php
namespace App\Repositories\Contracts;

interface BookingStatusLogRepositoryInterface
{
    public function paginateStatusLogs(int $bookingId, int $perPage = 10);
}
