<?php

namespace App\Http\Controllers;
use App\Http\Requests\storeCarFaultRequest;
use App\Services\Interfaces\CarFaultServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\CarFaultRequest;

use App\Http\Resources\CarFaultResource;

class CarFaultController extends Controller
{
    protected $service;

    public function __construct(CarFaultServiceInterface $service)
    {
        $this->service = $service;
    }

    public function store(StoreCarFaultRequest $request)
{
    $data = $request->validated();
    $data['trainer_id'] = auth()->user()->trainer->id;

    try {
        $fault = $this->service->submitFault($data);

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال العطل بنجاح، .',
            'data' => $fault
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'فشل العطل',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
public function index()
{
    try {
        $faults = $this->service->getAllLatestFaults();

            return CarFaultResource::collection($faults)->additional([
        'status' => 'success',
        'message' => 'تم استرجاع جميع اعطال السيارات بنجاح',
    ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء جلب الأعطال.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getTrainerFaults()
{
    $trainerId = auth()->user()->trainer->id; 

    $faults = $this->service->getFaultsByTrainer($trainerId);

          return CarFaultResource::collection($faults)->additional([
        'status' => 'success',
        'message' => 'تم استرجاع جميع اعطال السيارات  للمدرب بنجاح',
    ]);
}
public function sendToRepair(CarFaultRequest $request)
{
  

    $this->service->markCarAsInRepairByFault($request->fault_id);

    return response()->json([
        'status' => true,
        'message' => 'تم تحويل السيارة إلى التصليح بنجاح.',
    ]);
}

public function sendToResolved(CarFaultRequest $request)
{
   

    $this->service->markCarAsResolvedByFault($request->fault_id);

    return response()->json([
        'status' => true,
        'message' => 'تم تصليح السيارة بنجاح.',
    ]);
}

public function countFaultsPerCar()
{
    return response()->json([
        'data' => $this->service->countFaultsPerCar(),
    ]);
}

public function getTopFaultedCars()
{
    return response()->json([
        'data' => $this->service->getTopFaultedCars(5),
    ]);
}

public function getMonthlyFaultsCount(Request $request)
{
    return response()->json([
        'data' => $this->service->getMonthlyFaultsCount($request->input('year')),
    ]);
}

public function getAverageMonthlyFaultsPerCar(Request $request)
{
        $year = $request->input('year', now()->year);

    return response()->json([
            'message' => "الإحصائيات تحسب متوسط عدد الأعطال الشهري للسنة {$year}",

        'data' => $this->service->getAverageMonthlyFaultsPerCar($request->input('year')),
    ]);
}



public function getFaultsStatusCountPerCar()
{
    return response()->json([
        'data' => $this->service->getFaultsStatusCountPerCar(),
    ]);
}

}