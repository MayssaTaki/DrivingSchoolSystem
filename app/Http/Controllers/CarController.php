<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CarResource;
use App\Http\Requests\CarAddedRequest;
use App\Http\Requests\CarUpdateRequest;

use App\Exceptions\CarNotFoundException;

use Symfony\Component\HttpFoundation\Response;

use App\Models\Car;
use Illuminate\Http\Request;

use App\Services\CarService;


class CarController extends Controller
{

    public function __construct(carService $carService)
    {
        $this->carService = $carService;
    }

public function getAllCars(Request $request)
    {
        $make = $request->get('make');
        $cars = $this->carService->getAllCars($make);

        if ($cars->total() === 0) {
            return response()->json([
                'status' => 'fail',
                'message' => 'لم يتم العثور على سيارات',
                'data' => [],
            ], 404);
        }

       return CarResource::collection($cars)->additional([
        'status' => 'success',
        'message' => 'تم استرجاع السيارات بنجاح',
    ]);
    }
    public function countCars(): JsonResponse
{
    try {
        $carCount = $this->carService->countCars();
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب عدد السيارات   بنجاح.',
            'data' => [
                'car_count' => $carCount
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 403);
    }
}
public function add(CarAddedRequest $request): JsonResponse
{
    try {
        $data = $request->validated();
        $car = $this->carService->add($data);

        return response()->json([
            'status' => 'success',
            'message' => 'تم اضافة سيارة بنجاح.',
            'data' => new CarResource($car)
        ], 201);
    } catch (CarAddeException  $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ غير متوقع.',
        ], 500);
    }
}

    public function destroy($id): JsonResponse
{
   

    try {
        $this->carService->delete((int) $id);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف السيارة به بنجاح',
        ], Response::HTTP_OK);

    } catch (CarNotFoundException $e) {
        return response()->json([
            'status' => 'fail',
            'message' => $e->getMessage(),
        ], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ غير متوقع أثناء الحذف',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

public function update(CarUpdateRequest $request, Car $car): JsonResponse
{
    try {
        $updatedcar = $this->carService->update($car, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات السيارة بنجاح.',
            'data' => new CarResource($updatedcar)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
   
}
