<?php
namespace Database\Seeders;
use App\Models\LicenseRequest;
use App\Models\License;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;

class LicenseRequestSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        $statuses = ['pending', 'approved', 'rejected'];
        $types = ['new', 'renewal', 'replacement'];
        $notesList = [
            'الرجاء مراجعة البيانات المرفقة.',
            'تمت الموافقة على الطلب بعد التحقق.',
            'الوثائق غير مكتملة.',
            'تم رفض الطلب بسبب نقص في الملفات.',
            'بانتظار موافقة الإدارة.',
        ];

        $students = Student::with('user')->get(); 

        $licenses = License::all();

        foreach ($students as $student) {
            if ($licenses->isEmpty()) continue;

            $status = Arr::random($statuses);
            $type = Arr::random($types);
            $notes = Arr::random($notesList);

            $issuedAt = $status === 'approved' ? now()->subDays(rand(1, 30)) : null;
            $expiresAt = $issuedAt ? $issuedAt->copy()->addYear() : null;

            LicenseRequest::create([
                'student_id' => $student->id, 
                'license_id' => $licenses->random()->id,
                'status' => $status,
                'notes' => $notes,
                'type' => $type,
                'issued_at' => $issuedAt,
                'expires_at' => $expiresAt,
                'document_files' => json_encode([
                    'photo_id.jpg',
                    'proof_of_residence.pdf',
                    'previous_license.pdf'
                ]),
            ]);
        }
    }
}
