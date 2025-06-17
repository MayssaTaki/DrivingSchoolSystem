<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\CertificateServiceInterface;
use Illuminate\Http\JsonResponse;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateServiceInterface $certificateService)
    {
        $this->certificateService = $certificateService;
    }

   public function generate(int $studentId): JsonResponse
{
    $certificateUrl = $this->certificateService->generateOrGetCertificate($studentId);

    if (!$certificateUrl) {
        return response()->json([
            'message' => '❌ لم يجتز الطالب كل الفحوصات المطلوبة. الشهادة غير متاحة.',
        ], 403);
    }

    return response()->json([
        'message' => '✅ تم إنشاء شهادة النجاح بنجاح.',
        'certificate_url' => $certificateUrl,
    ]);
}


  public function download()
    {
        $studentId = auth()->user()->student->id;

        $certificatePath = $this->certificateService->generateOrGetCertificate($studentId);

        if (!$certificatePath || !file_exists($certificatePath)) {
            return response()->json([
                'message' => '❌ لم يجتز الطالب كل الفحوصات أو لم يتم العثور على الشهادة.'
            ], 403);
        }

        return response()->download($certificatePath, 'شهادة_نجاح_الطالب.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

}
