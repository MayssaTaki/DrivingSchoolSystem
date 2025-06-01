<?php
namespace App\Repositories\Contracts;

interface CertificateRepositoryInterface
{
    public function generateCertificate(int $studentId): string;
}
