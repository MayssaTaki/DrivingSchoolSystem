<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleExceptionRequest;
use App\Services\ScheduleExceptionService;
use App\Models\ScheduleException;
use App\Http\Resources\ScheduleExceptionResource;
use App\Http\Requests\GetTrainerExceptionsRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleExceptionController extends Controller
{
    protected $service;

    public function __construct(ScheduleExceptionService $service)
    {
        $this->service = $service;
    }

     public function store(ScheduleExceptionRequest $request): JsonResponse
    {
        $trainerId = $request->input('trainer_id');
        $dates = $request->input('exception_dates');
        $reason = $request->input('reason');

        $exceptions = $this->service->createExceptions($trainerId, $dates, $reason);

        return response()->json([
            'message' => 'تم تسجيل الإجازات  بنجاح.',
            'data' => $exceptions,
        ], 201);
    }

    public function approve(int $id): JsonResponse
{
    $exception = $this->service->approveException($id);

    if (!$exception) {
        return response()->json([
            'message' => 'طلب الإجازة غير صالح أو تمت معالجته مسبقًا.'
        ], 400);
    }

    return response()->json([
        'message' => 'تمت الموافقة على الإجازة وتم إلغاء الجلسات بنجاح.',
        'data' => $exception
    ]);
}
public function reject(int $id): JsonResponse
{
    $exception = $this->service->rejectException($id);

    if (!$exception) {
        return response()->json([
            'message' => 'طلب الإجازة غير صالح أو تمت معالجته مسبقًا.'
        ], 400);
    }

    return response()->json([
        'message' => 'تم رفض الإجازة بنجاح.',
        'data' => $exception
    ]);
}

 public function getTrainerExceptions(GetTrainerExceptionsRequest $request): JsonResponse
{
    $exceptions = $this->service->getAllExceptionsByTrainer($request->trainer_id);

    return response()->json([
        'status' => 'success',
        'data' => ScheduleExceptionResource::collection($exceptions),
    ]);
}

public function getAllTrainersExceptions(): JsonResponse
{
    $exceptions = $this->service->getAllTrainersExceptions();
    
    $resource = ScheduleExceptionResource::collection($exceptions);
    $resource->additional([
        'status' => 'success',
        'message' => 'تم استرجاع اجازات المدربين بنجاح'
    ]);
    
    return $resource->response();
}
}
