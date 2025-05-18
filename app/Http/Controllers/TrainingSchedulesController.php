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


use Illuminate\Auth\Access\AuthorizationException;



class TrainingSchedulesController extends Controller
{
    protected $trainingService;

    public function __construct(TrainingSchedulesService $trainingService)
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
    $schedule = $this->trainingService->activate($id);
    return response()->json([
        'message' => 'تم تفعيل الجدول بنجاح.',
        
    ]);
}

public function deactivate($id)
{
    $schedule = $this->trainingService->deactivate($id);
    return response()->json([
        'message' => 'تم تعطيل الجدول بنجاح.',
       
    ]);
}


}