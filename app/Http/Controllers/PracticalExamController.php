<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\PracticalExamServiceInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PracticalExamScheduleStoreRequest;
use Illuminate\Http\Request;



class PracticalExamController extends Controller
{
    protected PracticalExamServiceInterface $practical;

    public function __construct(PracticalExamServiceInterface $practical)
    {
        $this->practical = $practical;
 
   }

   public function store(PracticalExamScheduleStoreRequest $req): JsonResponse
    {
        $schedule = $this->practical->scheduleExam($req->validated());
        return response()->json(['success'=>true,'data'=>$schedule], 201);
    }

      public function index(Request $request): JsonResponse
    {
        $schedules = $this->practical->listAll(10);
        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    public function mySchedules(): JsonResponse
    {
        $schedules = $this->practical->getMySchedules();

        return response()->json([
            'success' => true,
            'data' => $schedules,
        ]);
    }

    public function markAsPassed($id): JsonResponse
{
    $this->practical->markAsPassed($id);
    return response()->json(['message' => 'تم تعيين الحالة: ناجح']);
}

public function markAsFailed($id): JsonResponse
{
    $this->practical->markAsFailed($id);
    return response()->json(['message' => 'تم تعيين الحالة: راسب']);
}

public function markAsAbsent($id): JsonResponse
{
    $this->practical->markAsAbsent($id);
    return response()->json(['message' => 'تم تعيين الحالة: غائب']);
}

 public function countByStatus(Request $req)
    {
        return response()->json([
            'success' => true,
            'data' => $this->practical->getCountByStatus($req->only(['from','to']))
        ]);
    }

    public function failedOrAbsentStudents(Request $req)
    {
        return response()->json([
            'success' => true,
            'data' => $this->practical->getFailedOrAbsentStudents($req->only(['from','to']))
        ]);
    }

    public function successRatio(Request $req)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'success_ratio' => $this->practical->getSuccessRatio($req->only(['from','to'])),
            ],
        ]);
    }

}
