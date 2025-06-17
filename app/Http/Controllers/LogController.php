<?php
namespace App\Http\Controllers;

use App\Services\Interfaces\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\LogEntryResource;

class LogController extends Controller
{
    protected LogServiceInterface $logService;

    public function __construct(LogServiceInterface $logService)
    {
       
        $this->logService = $logService;
    }

    public function index(Request $request)
    {
        $logs = $this->logService->getPaginatedLogs(
            $request->get('per_page', 10),
            $request->get('level'),
            $request->get('channel')
        );
    
        return LogEntryResource::collection($logs)->additional([
            'status' => 'success',
        ]);
    }
    
}
