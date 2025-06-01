<?php
namespace App\Repositories;

use App\Repositories\Contracts\CertificateRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;

class CertificateRepository implements CertificateRepositoryInterface
{
   public function generateCertificate(int $studentId): string
    {
        $fileName = "certificate_{$studentId}.pdf";
        $storagePath = "certificates/{$fileName}";

        if (Storage::disk('public')->exists($storagePath)) {
            return Storage::disk('public')->path($storagePath);
        }

        $student = Student::findOrFail($studentId);
        $data = [
            'name' => $student->name,
            'date' => now()->format('Y-m-d'),
        ];

        $pdf = Pdf::loadView('certificates.success', $data);
        Storage::disk('public')->put($storagePath, $pdf->output());

        return Storage::disk('public')->path($storagePath);
    }
}
