<?php
namespace App\Services;

use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Services\ExamServiceInterface;

class CertificateService
{
    protected CertificateRepositoryInterface $certificateRepo;
    protected ExamService $Service;

    public function __construct(
        CertificateRepositoryInterface $certificateRepo,
        ExamService $Service
    ) {
        $this->certificateRepo = $certificateRepo;
        $this->Service = $Service;
    }

    public function generateOrGetCertificate(int $studentId): ?string
    {
        $evaluation = $this->Service->evaluateStudent($studentId);

        if (!$evaluation['passed_all']) {
            return null;
        }

        return $this->certificateRepo->generateCertificate($studentId);
    }
}
