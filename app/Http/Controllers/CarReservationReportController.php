<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\CarReservationReportServiceInterface;

class CarReservationReportController extends Controller
{
    protected $service;

    public function __construct(CarReservationReportServiceInterface $service)
    {
        $this->service = $service;
    }

    public function reportByCar($carId)
    {
        return response()->json($this->service->getCarReport($carId));
    }

    public function reportByDateRange(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        return response()->json(
            $this->service->getReportByDateRange($request->from, $request->to)
        );
    }
}
