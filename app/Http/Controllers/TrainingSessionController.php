<?php
namespace App\Http\Controllers;

use App\Services\TrainingSessionService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TrainerSessionDayResource;


class TrainingSessionController extends Controller
{
    public function __construct(private TrainingSessionService $sessionService) {}

   public function getTrainerSessions(): JsonResponse
{
    $trainer = auth()->user()->trainer;

    if (!$trainer) {
        return response()->json([
            'message' => 'المستخدم الحالي ليس له حساب مدرب.'
        ], 403);
    }

    $sessions = $this->sessionService->getTrainerSessions($trainer->id);

    $grouped = $sessions->groupBy('session_date')->map(function ($sessions, $date) {
        return (object)[
            'date' => $date,
            'sessions' => $sessions->sortBy('start_time')->values()
        ];
    })->values();

    return response()->json([
        'message' => 'تم جلب الجلسات بنجاح.',
        'data' => TrainerSessionDayResource::collection($grouped)
    ]);
}}
