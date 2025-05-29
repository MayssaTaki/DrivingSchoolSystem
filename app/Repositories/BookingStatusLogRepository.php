<?php
namespace App\Repositories;
use App\Repositories\Contracts\BookingStatusLogRepositoryInterface;

use App\Models\BookingStatusLog;

class BookingStatusLogRepository implements BookingStatusLogRepositoryInterface
{
    public function paginateStatusLogs(int $bookingId, int $perPage = 10)
    {
        return BookingStatusLog::where('booking_id', $bookingId)
            ->with('changer')
            ->orderBy('changed_at', 'desc')
            ->paginate($perPage);
    }
}
