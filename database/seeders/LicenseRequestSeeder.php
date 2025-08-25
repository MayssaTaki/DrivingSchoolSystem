<?php

namespace Database\Seeders;

use App\Models\LicenseRequest;
use App\Models\License;
use App\Models\Student;
use App\Models\PaymentTransaction;
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

        if ($students->isEmpty() || $licenses->isEmpty()) {
            $this->command->warn("⚠️ لا يوجد طلاب أو رخص في قاعدة البيانات.");
            return;
        }

        for ($i = 0; $i < 60; $i++) {
            $student = $students->random();
            $license = $licenses->random();

            $status = Arr::random($statuses);
            $type = Arr::random($types);
            $notes = Arr::random($notesList);

            if ($status === 'rejected') {
                $payment = PaymentTransaction::where('status', 'refund_confirmed')->inRandomOrder()->first();
            } elseif ($status === 'approved') {
                $payment = PaymentTransaction::where('status', 'confirmed')->inRandomOrder()->first();
            } elseif ($status === 'pending') {
                $payment = PaymentTransaction::whereIn('status', ['created', 'confirmed'])->inRandomOrder()->first();
            } else {
                $payment = PaymentTransaction::inRandomOrder()->first(); // fallback
            }

            if (!$payment) {
                $this->command->warn("⚠️ لا يوجد معاملات دفع مناسبة للحالة: $status");
                continue;
            }

            $issuedAt = $status === 'approved' ? now()->subDays(rand(1, 30)) : null;
            $expiresAt = $issuedAt ? $issuedAt->copy()->addYear() : null;

            LicenseRequest::create([
                'student_id' => $student->id,
                'license_id' => $license->id,
                'payment_transaction_id' => $payment->id,
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
