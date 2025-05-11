<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Trainer;
use Illuminate\Http\Request;
use App\Services\TrainingSchedulesService;
use App\Http\Resources\TrainingSchedulesResource;

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
}