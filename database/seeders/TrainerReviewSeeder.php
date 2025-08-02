<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainerReview;
use App\Models\TrainingSession;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;

class TrainerReviewSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ar_SA');

        $sessions = TrainingSession::where('status', 'completed')
            ->whereHas('bookings') 
            ->with('bookings.student', 'trainer.user')
            ->get()
->filter(function ($session) {
    return $session->bookings->isNotEmpty() && $session->bookings->first()->student_id;
});

        if ($sessions->isEmpty()) {
            $this->command->warn('⚠️ لا توجد جلسات مكتملة بحجوزات طلاب.');
            return;
        }

        $created = 0;

    foreach ($sessions as $session) {
    $booking = $session->bookings->first();

    if (!$booking || !$booking->student_id) continue;

    $studentId = $booking->student_id;
    $trainerId = $session->trainer_id;

            $alreadyReviewed = TrainerReview::where('student_id', $studentId)
                ->where('trainer_id', $trainerId)
                ->exists();

            if ($alreadyReviewed) continue;

            $rating = rand(1, 5); 

            $status = match (true) {
                $rating >= 4 => 'approved',
                $rating <= 2 => 'rejected',
                default      => 'pending',
            };

            $comment = match ($rating) {
                5 => 'أفضل مدرب! أنصح به بشدة.',
                4 => 'مدرب جيد جدًا وشرح واضح.',
                3 => 'جيد نوعًا ما لكن يمكن تحسين بعض الأمور.',
                2 => 'ضعيف في إيصال المعلومة ويحتاج تحسين.',
                1 => 'تجربة سيئة جدًا، لا أنصح به.',
            };

            TrainerReview::create([
                'student_id' => $studentId,
                'trainer_id' => $trainerId,
                'rating'     => $rating,
                'comment'    => $comment,
                'status'     => $status,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);

            $created++;
        }

        $this->command->info("✅ تم إنشاء {$created} تقييم شامل (مقبول + مرفوض + معلق) لكل طالب ومدرب.");
    }
}
