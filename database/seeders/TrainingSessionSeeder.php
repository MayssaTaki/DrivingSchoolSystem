<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingSession;
use App\Models\Trainer;
use App\Models\TrainingSchedule;

class TrainingSessionSeeder extends Seeder
{
    public function run()
    {
        $trainer = Trainer::first();
        $schedule = TrainingSchedule::first();

        if (!$trainer || !$schedule) {
            $this->command->warn('لا يوجد مدرب أو جدول، الرجاء التأكد من تشغيل TrainerSeeder و TrainingScheduleSeeder أولاً.');
            return;
        }

        for ($i = 0; $i < 5; $i++) {
            TrainingSession::create([
                'trainer_id' => $trainer->id,
                'schedule_id' => $schedule->id,
                'session_date' => now()->addDays($i),
                'start_time' => '10:00',
                'end_time' => '11:00',
                'status' => 'available',
            ]);
        }
    }
}
