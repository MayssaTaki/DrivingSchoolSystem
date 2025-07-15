<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainerReview;
use App\Models\TrainingSession;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class TrainerReviewSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ar_SA');

        $sessions = TrainingSession::where('status', 'completed')
            ->with('trainer', 'booking.student')
            ->get()
            ->filter(function ($session) {
                return optional($session->booking)->student_id !== null;
            });

        if ($sessions->isEmpty()) {
            $this->command->warn('⚠️ لا توجد جلسات محجوزة بها طلاب لتوليد التقييمات.');
            return;
        }

        $reviewsCount = 20;
        $comments = [
            'مدرب ممتاز وشرح واضح.',
            'التجربة كانت جيدة جدًا.',
            'يحتاج لتحسين طريقة الشرح.',
            'تعامل احترافي وصبور.',
            'غير ملتزم بالمواعيد أحيانًا.',
            'أفضل مدرب تعاملت معه.',
            'الشرح بسيط وسهل الفهم.',
            'لا يجيب على كل الأسئلة بوضوح.',
            'ينصح به للمبتدئين.',
            'تجربة متوسطة.',
        ];

        $reviewed = [];

        foreach (range(1, $reviewsCount) as $i) {
            $session = $sessions->random();
            $studentId = optional($session->booking)->student_id;
            $trainerId = $session->trainer_id;

            $key = $studentId . '_' . $trainerId;
            if (in_array($key, $reviewed)) {
                continue;
            }
            $reviewed[] = $key;

            TrainerReview::create([
                'student_id' => $studentId,
                'trainer_id' => $trainerId,
                'rating'     => rand(1, 5),
                'comment'    => Arr::random($comments),
                'status'     => Arr::random(['approved', 'pending', 'rejected']),
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ تم إنشاء تقييمات عربية لمدربين فقط مع طلاب لديهم جلسات محجوزة.');
    }
}
