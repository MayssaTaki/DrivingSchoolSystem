<?php
namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\LicenseStoreRequest;
use App\Http\Requests\LicenseUpdateRequest;


use App\Services\Interfaces\LicenseServiceInterface;

class LicenseController extends Controller
{
protected LicenseServiceInterface $licenseService;

    public function __construct(LicenseServiceInterface $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function index(Request $request): JsonResponse
{
    $code = $request->query('code'); 

    $licenses = $this->licenseService->listLicenses($code);

    if ($licenses->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'لم يتم العثور على شهادات.',
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => $licenses,
    ]);
}
public function store(LicenseStoreRequest $request): JsonResponse
{
    $license = $this->licenseService->createLicense($request->validated());

    return response()->json([
        'status' => 'success',
        'message' => 'تم إنشاء الشهادة بنجاح.',
        'data' => $license
    ], 201);
}
public function update(LicenseUpdateRequest $request, int $id): JsonResponse
{
    $license = $this->licenseService->updateLicense($id, $request->validated());

    return response()->json([
        'status' => 'success',
        'message' => 'تم تحديث الشهادة بنجاح.',
        'data' => $license,
    ]);
}
   public function countLicenses(): JsonResponse
{
    try {
        $licenseCount = $this->licenseService->countLicenses();
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب عدد الشهادات   بنجاح.',
            'data' => [
                'license_count' => $licenseCount
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 403);
    }
}

}