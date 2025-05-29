<?php

namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\TrainingSchedule;
use Illuminate\Database\Seeder;

class TrainingScheduleSeeder extends Seeder
{
    public function run(): void
    {
        

        $trainers = Trainer::pluck('id')->toArray();
        if (empty($trainers)) {
            $this->command->warn('⚠️ لا يوجد مدربين في قاعدة البيانات.');
            return;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'saturday', 'sunday'];

        foreach (range(1, 10) as $i) {
            TrainingSchedule::create([
                'trainer_id'   => $trainers[array_rand($trainers)],
                'day_of_week'  => $days[array_rand($days)],
                'start_time'   => fake()->time('H:i', '17:00'), 
                'end_time'     => fake()->time('H:i', '20:00'), 
                'is_recurring' => true,
                'valid_from'   => now()->startOfWeek(),
                'valid_to'     => now()->addMonth(),
                'status'       => 'inactive',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

    }
}
