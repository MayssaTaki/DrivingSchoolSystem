<?php

namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\Student;
use App\Models\TrainerReview;
use Illuminate\Database\Seeder;

class TrainerReviewSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::pluck('id')->toArray();
        $trainers = Trainer::pluck('id')->toArray();

        if (empty($students) || empty($trainers)) {
            $this->command->warn('⚠️ لا يوجد طلاب أو مدربين لإنشاء التقييمات');
            return;
        }

        foreach (range(1, 10) as $i) {
            TrainerReview::create([
                'student_id' => $students[array_rand($students)],
                'trainer_id' => $trainers[array_rand($trainers)],
                'rating' => rand(1, 5),
                'comment' => fake()->optional()->sentence(),
                'status' => collect(['pending', 'approved', 'rejected'])->random(),
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now(),
            ]);
        }

    }
}
