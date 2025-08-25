<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\PracticalExamServiceInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PracticalExamScheduleStoreRequest;
use Illuminate\Http\Request;
use App\Http\Resources\PracticalExamResource;
use App\Services\Interfaces\LicenseRequestServiceInterface;



class PracticalExamController extends Controller
{
    protected PracticalExamServiceInterface $practical;

    public function __construct(PracticalExamServiceInterface $practical,LicenseRequestServiceInterface $licenseService)
    {
        $this->practical = $practical;
         $this->licenseService = $licenseService;

   }

   public function store(PracticalExamScheduleStoreRequest $req): JsonResponse
    {
        $schedule = $this->practical->scheduleExam($req->validated());
        return response()->json(['success'=>true,'data'=>$schedule], 201);
    }

      public function index(Request $request)
    {
        $schedules = $this->practical->listAll(10);
        return PracticalExamResource::collection( $schedules)->additional([
            'success' => true,
            
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

    public function markAsPassed(Request $request,$id): JsonResponse
{
    $request->validate([
            'issued_at' => 'required|date',
            'expires_at' => 'required|date|after_or_equal:issued_at',
        ]);

        $issuedAt = $request->input('issued_at');
        $expiresAt = $request->input('expires_at');
   $schedule = $this->practical->markAsPassed($id);

    if (!$schedule) {
        return response()->json(['message' => 'تعذر تحديث حالة الامتحان'], 422);
    }
    $this->licenseService->issueLicenseAfterExam($schedule, $issuedAt, $expiresAt);

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
