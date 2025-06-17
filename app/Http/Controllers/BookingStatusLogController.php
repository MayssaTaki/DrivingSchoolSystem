<?php
namespace App\Http\Controllers;
use App\Http\Resources\BookingStatusLogResource;

use App\Services\Interfaces\BookingStatusLogServiceInterface;
use Illuminate\Http\Request;

class BookingStatusLogController extends Controller
{
    protected $statusLogService;

    public function __construct(BookingStatusLogServiceInterface $statusLogService)
    {
        $this->statusLogService = $statusLogService;
    }

   public function index(Request $request)
{
    $perPage = $request->get('per_page', 10); 
    $logs = $this->statusLogService->getPaginatedStatusLogs($perPage);

    return BookingStatusLogResource::collection($logs)
        ->additional([
            'message' => 'تم بنجاح',
            'status' => 'success',
          
        ]);
}

  public function export()
{
    return $this->statusLogService->exportBookingStatusLogs();
}

public function exportPdf()
{
    return $this->statusLogService->exportBookingStatusLogsPdf();
}
}
