<?php
namespace App\Repositories;
use App\Repositories\Contracts\BookingStatusLogRepositoryInterface;
use App\Exports\BookingStatusLogsExport;
use PDF;
use App\Models\BookingStatusLog;
use Maatwebsite\Excel\Facades\Excel;

class BookingStatusLogRepository implements BookingStatusLogRepositoryInterface
{
    public function paginateStatusLogs(int $perPage = 10)
{
    return BookingStatusLog::with(['changer', 'booking.session']) 
        ->orderBy('changed_at', 'desc')
        ->paginate($perPage);
}

  public function exportBookingStatusLogs()
{
    return Excel::download(new BookingStatusLogsExport(), 'booking_status_logs.xlsx');
}

public function exportBookingStatusLogsPdf()
{
    $logs = BookingStatusLog::with(['booking.session', 'changer'])
        ->orderBy('changed_at', 'desc')
        ->get();

    $pdf = PDF::loadView('exports.booking_status_logs_pdf', compact('logs'));
    return $pdf->download('booking_status_logs.pdf');
}

}
