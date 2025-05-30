<?php
namespace App\Http\Controllers;
use App\Http\Requests\GetSessionCountsRequest;
use App\Http\Requests\RecommendedSessionRequest;


use App\Services\TrainingSessionService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TrainerSessionDayResource;
use App\Http\Requests\GetTrainerSessionsRequest;
use Illuminate\Http\Request;


class TrainingSessionController extends Controller
{
    public function __construct(private TrainingSessionService $sessionService) {}

 public function getTrainerSessions(GetTrainerSessionsRequest $request): JsonResponse
{
    $trainerId = $request->input('trainer_id');

    $grouped = $this->sessionService->getTrainerSessionsGroupedByDate($trainerId);

    return response()->json([
        'message' => 'تم جلب الجلسات بنجاح.',
        'data' => TrainerSessionDayResource::collection($grouped)
    ]);
}

public function getRecommendedSessions(RecommendedSessionRequest $request)
{
    $studentId = auth()->user()->student->id;

    $sessions = $this->sessionService->getRecommendedSessions(
        $studentId,
        $request->input('preferred_date'),
        $request->input('preferred_time'),
        $request->input('training_type')
    );

    return response()->json([
        'message' => 'قائمة الجلسات المقترحة بنجاح',
        'data' => $sessions,
    ]);
}





 public function getSessionCounts(GetSessionCountsRequest $request)
{
    $trainerId = $request->input('trainer_id');
    $month = $request->input('month'); 

    $counts = $this->sessionService->getSessionCounts($trainerId, $month);

    return response()->json([
        'message' => 'تم جلب إحصائيات الجلسات بنجاح.',
        'data' => $counts,
    ]);
}

}
