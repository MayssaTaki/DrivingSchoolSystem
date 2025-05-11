<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use App\Http\Resources\ActivityLogResource;

class ActivityLogController extends Controller
{
    protected ActivityLogService $service;

    public function __construct(ActivityLogService $service)
    {
        $this->service = $service;
    }

    public function getAllActivityLogs(Request $request)
{
    $filters = $request->only(['log_name', 'event', 'search', 'user_id', 'batch_uuid']);
    $logs = $this->service->getPaginatedLogs($filters, 10);

    if ($logs->total() === 0) {
        return response()->json([
            'status' => 'fail',
            'message' => 'لم يتم العثور على سجلات نشاط',
            'data' => [],
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'تم استرجاع سجلات النشاط بنجاح',
        'data' => ActivityLogResource::collection($logs), 
    ]);
}

}
