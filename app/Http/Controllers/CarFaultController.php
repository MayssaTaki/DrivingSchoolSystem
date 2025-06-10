<?php

namespace App\Http\Controllers;
use App\Http\Requests\storeCarFaultRequest;
use App\Services\CarFaultService;
use Illuminate\Http\JsonResponse;

class CarFaultController extends Controller
{
    protected $service;

    public function __construct(CarFaultService $service)
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
}}