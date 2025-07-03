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
}
