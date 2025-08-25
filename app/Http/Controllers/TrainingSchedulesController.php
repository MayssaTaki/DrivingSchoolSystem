<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Trainer;
use Illuminate\Http\Request;
use App\Services\TrainingSchedulesService;
use App\Http\Resources\TrainingSchedulesResource;
use App\Http\Requests\StoreTrainingScheduleRequest;
use App\Http\Requests\UpdateTrainingScheduleRequest;
use App\Services\Interfaces\TrainingSchedulesServiceInterface;


use Illuminate\Auth\Access\AuthorizationException;



class TrainingSchedulesController extends Controller
{
    protected $trainingService;

    public function __construct(TrainingSchedulesServiceInterface $trainingService)
    {
        $this->trainingService = $trainingService;
    }


 public function showByTrainer($trainerId)
{
    try {
        $schedules = $this->trainingService->getTrainerSchedules($trainerId);
        
        return TrainingSchedulesResource::collection($schedules->items());
        
    } catch (\Exception $e) {
        $statusCode = $e->getCode() >= 400 && $e->getCode() < 500 ? $e->getCode() : 500;
        
        return response()->json([
            'error' => $e->getMessage(),
            'trainer_id' => $trainerId
        ], $statusCode);
    }
}
public function store(StoreTrainingScheduleRequest $request)
{
    $schedules = $this->trainingService->createMany($request->validated()['schedules']);
 return response()->json([
        'message' => ' تم انشاء جدول التدريب الخاص بك  بنجاح.',
 'data' => $schedules->toArray()
    ]);
}


public function update(UpdateTrainingScheduleRequest $request)
{
    $schedules = $this->trainingService->updateMany($request->validated()['schedules']);

return response()->json([
        'message' => ' تم تعديل جدول التدريب الخاص بك  بنجاح.',
         'data' => $schedules->toArray()

    ]);}

public function activate($id)
{
    try {
        $schedule = $this->trainingService->activate($id);
        return response()->json([
            'message' => 'تم تفعيل الجدول بنجاح.',
            'data' => $schedule
        ]);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        return response()->json([
            'message' => $e->getMessage()
        ], $e->getStatusCode());
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ غير متوقع، يرجى المحاولة لاحقاً.'
        ], 500);
    }
}


public function deactivate($id)
{
    $schedule = $this->trainingService->deactivate($id);
    return response()->json([
        'message' => 'تم تعطيل الجدول بنجاح.',
       
    ]);
}
public function setFee(Request $request, int $id) {
        $validated = $request->validate([
            'registration_fee' => 'required|integer|min:0'
        ]);

        $schedule = $this->trainingService->updateFee($id, $validated['registration_fee']);

        return response()->json($schedule);
    }

}