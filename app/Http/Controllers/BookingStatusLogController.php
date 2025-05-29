<?php
namespace App\Http\Controllers;
use App\Http\Resources\BookingStatusLogResource;

use App\Services\BookingStatusLogService;
use Illuminate\Http\Request;

class BookingStatusLogController extends Controller
{
    protected $statusLogService;

    public function __construct(BookingStatusLogService $statusLogService)
    {
        $this->statusLogService = $statusLogService;
    }

    public function index(Request $request, $bookingId)
    {
        $perPage = 10;
        $logs = $this->statusLogService->getPaginatedStatusLogs($bookingId, $perPage);

     return BookingStatusLogResource::collection($logs)
        ->additional(['message' => 'بنجاح']);
    }
}
