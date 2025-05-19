<?php
namespace App\Http\Controllers;

use App\Services\TrainingSessionService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TrainerSessionDayResource;
use Illuminate\Http\Request;


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
}

 public function getSessionCounts(Request $request)
    {
        $user = $request->user();

        $trainerId = $request->input('trainer_id');
        $month = $request->input('month'); 

        $counts = $this->sessionService->getSessionCounts($trainerId, $month);

        return response()->json([
            'message' => 'تم جلب إحصائيات الجلسات بنجاح.',
            'data' => $counts,
        ]);
    }

}
