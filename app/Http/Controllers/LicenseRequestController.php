<?php
namespace App\Http\Controllers;
use App\Http\Requests\LicenseRequestStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\LicenseRequestResource;


use App\Services\Interfaces\LicenseRequestServiceInterface;

class LicenseRequestController extends Controller
{
protected LicenseRequestServiceInterface $licenseService;

    public function __construct(LicenseRequestServiceInterface $licenseService)
    {
        $this->licenseService = $licenseService;
    }

 public function store(LicenseRequestStore $request): JsonResponse
{
    try {
        $data = $request->validated();
        $licenseRequest = $this->licenseService->requestLicense($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم الطلب بنجاح.',
            'data' => $licenseRequest
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 422);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ غير متوقع أثناء تنفيذ العملية. يرجى المحاولة لاحقًا.',
        ], 500);
    }
}

public function index()
{
    $requests = $this->licenseService->getAllRequests(10);
    return LicenseRequestResource::collection($requests)->additional([
       'status' => 'success',
        'message' => 'تم استرجاع طلبات الرخص بنجاح',
    ]);
}

public function myRequests(): JsonResponse
{
    $requests = $this->licenseService->getRequestsForCurrentStudent();
    return   response()->json([
        'success' => true,
        'data' => LicenseRequestResource::collection($requests)
    ]);
}

public function approve(int $id): JsonResponse
{
    $this->licenseService->approveRequest($id);

    return response()->json([
        'success' => true,
        'message' => 'تمت الموافقة على الطلب بنجاح.',
    ]);
}

public function reject(Request $request, int $id): JsonResponse
{
    $request->validate([
        'reason' => 'required|string|max:1000'
    ]);

    $this->licenseService->rejectRequest($id, $request->reason);

    return response()->json([
        'success' => true,
        'message' => 'تم رفض الطلب بنجاح.',
    ]);
}

 public function getPending(): JsonResponse
    {
        $requests = $this->licenseService->getPendingRequests();

        return LicenseRequestResource::collection($requests)
            ->additional([
                'status' => 'success',
                'message' => 'تم استرجاع طلبات الرخص التي قيد الانتظار'
            ])
            ->response();
    }

    public function getApproved(): JsonResponse
    {
        $requests = $this->licenseService->getApprovedRequests();

        return LicenseRequestResource::collection($requests)
            ->additional([
                'status' => 'success',
                'message' => 'تم استرجاع طلبات الرخص الموافق عليها'
            ])
            ->response();
    }

    public function getRejected(): JsonResponse
    {
        $requests = $this->licenseService->getRejectedRequests();

        return LicenseRequestResource::collection($requests)
            ->additional([
                'status' => 'success',
                'message' => 'تم استرجاع طلبات الرخص المرفوضة'
            ])
            ->response();
    }

    public function countPending()
    {
        $requestsCount = $this->licenseService->countPendingRequests();

       return response()->json([
            'status' => 'success',
            'message' => 'تم جلب عدد طلبات الرخص المنتظرة  بنجاح.',
            'data' => [
                'licenseRequest_count' =>  $requestsCount
            ]
        ]);
    }

    public function countApproved()
    {
        $requestsCount = $this->licenseService->countApprovedRequests();

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب عدد طلبات الرخص الموافق عليها  بنجاح.',
            'data' => [
                'licenseRequest_count' =>  $requestsCount
            ]
        ]);
    }

    public function countRejected()
    {
        $requestsCount = $this->licenseService->countRejectedRequests();

              return response()->json([
            'status' => 'success',
            'message' => 'تم جلب عدد طلبات الرخص المرفوضة  بنجاح.',
            'data' => [
                'licenseRequest_count' =>  $requestsCount
            ]
        ]);
    }

    public function monthly(Request $r)
    {
        $data = $this->licenseService->getMonthlyReport(
            $r->get('year', date('Y')),
            $r->get('license_code'),
            $r->get('status')
        );
        return response()->json(['success'=>true,'data'=>$data]);
    }
    
    public function typeStats()
    {
        return response()->json(['success'=>true,'data'=>$this->licenseService->getTypeReport()]);
    }

    public function mostRequestedLicenses(Request $request): JsonResponse
{
    $limit = $request->get('limit', 2);
    $data = $this->licenseService->getMostRequestedLicenses($limit);

    return response()->json([
        'success' => true,
        'data' => $data,
    ]);
}

}