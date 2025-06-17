<?php

namespace App\Services\Interfaces;

interface CertificateServiceInterface
{
    /**
     * Generate or retrieve a certificate for the given student.
     *
     * @param int $studentId
     * @return string|null
     */
    public function generateOrGetCertificate(int $studentId): ?string;
}
