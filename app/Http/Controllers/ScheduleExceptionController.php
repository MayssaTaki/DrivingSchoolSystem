<?php
namespace App\Http\Controllers;

use App\Http\Requests\ScheduleExceptionRequest;
use App\Services\ScheduleExceptionService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ScheduleExceptionResource;


class ScheduleExceptionController extends Controller
{
    public function __construct(private ScheduleExceptionService $service)
    {
    }

    public function index(int $trainerId): JsonResponse
{
    try {
        $exceptions = $this->service->getTrainerExceptions($trainerId);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب استثناءات الجدولة بنجاح.',
            'data' => ScheduleExceptionResource::collection($exceptions)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء جلب استثناءات الجدولة.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function show(int $id): JsonResponse
    {
        $exception = $this->service->getException($id);
        return response()->json([
            'success' => true,
            'message' => 'تم جلب استثناء الجدولة بنجاح.',
            'data' => new ScheduleExceptionResource($exception)
        ]);
    }

    public function store(ScheduleExceptionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $exception = $this->service->createException($data);
        
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء استثناء الجدولة بنجاح.',
            'data' => $exception
        ], 201);
    }


  
}