<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarLocationRequest;
use App\Services\Interfaces\CarLocationServiceInterface;

class CarLocationController extends Controller
{
    protected $service;

    public function __construct(CarLocationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function store(StoreCarLocationRequest $request)
    {
        $location = $this->service->store($request->validated());

        return response()->json([
            'status' => true,
            'message' => '✅ تم حفظ الموقع بنجاح',
            'data' => $location,
        ]);
    }

    public function showLastLocation($carId)
    {
        $location = $this->service->getLastLocation($carId);

        if (!$location) {
            return response()->json(['status' => false, 'message' => '🚫 لا يوجد موقع مسجل لهذه السيارة'], 404);
        }

        return response()->json(['status' => true, 'data' => $location]);
    }

    
    public function showCarTrack($carId)
    {
        $locations = $this->service->getLocationsForCar($carId);
        return response()->json($locations);
    }

    public function showActiveCars()
    {
        $locations = $this->service->getLastLocationsForActiveCars();
        return response()->json($locations);
    }
}
