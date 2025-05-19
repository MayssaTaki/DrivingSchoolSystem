<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleExceptionRequest;
use App\Services\ScheduleExceptionService;
use App\Models\ScheduleException;
use Illuminate\Http\JsonResponse;

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

    public function update(ScheduleExceptionRequest $request, ScheduleException $exception): JsonResponse
    {
        $this->service->updateException($exception, $request->validated());

        return response()->json([
            'message' => 'تم تحديث الإجازة بنجاح.',
            'data' => $exception->fresh(),
        ]);
    }

    public function destroy(ScheduleException $exception): JsonResponse
    {
        $this->service->deleteException($exception);

        return response()->json([
            'message' => 'تم حذف الإجازة بنجاح.',
        ]);
    }
}
