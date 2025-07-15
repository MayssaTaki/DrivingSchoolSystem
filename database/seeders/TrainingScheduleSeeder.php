<?php
namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\TrainingSchedule;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TrainingScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $trainers = Trainer::where('status', 'approved')->get();

        if ($trainers->isEmpty()) {
            $this->command->warn('⚠️ لا يوجد مدربين بحالة موافقة (approved) لإنشاء جداول تدريب.');
            return;
        }

        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
        $timeSlots = [
            ['09:00', '10:00'],
            ['10:00', '12:00'],
            ['12:00', '13:00'],
            ['13:00', '15:00'],
            ['15:00', '17:00'],
            ['17:00', '19:00'],
            ['19:00', '20:00'],
        ];

        foreach ($trainers as $trainer) {
            $usedSlots = [];

            $schedulesCount = rand(2, 4);
            for ($i = 0; $i < $schedulesCount; $i++) {
                $day = $days[array_rand($days)];
                $slot = $timeSlots[array_rand($timeSlots)];

                $key = $day . '-' . $slot[0];
                if (in_array($key, $usedSlots)) {
                    continue;
                }
                $usedSlots[] = $key;

                TrainingSchedule::create([
                    'trainer_id' => $trainer->id,
                    'day_of_week' => $day,
                    'start_time' => $slot[0],
                    'end_time' => $slot[1],
                    'is_recurring' => true,
                    'valid_from' => Carbon::now()->addDays(rand(0, 5))->toDateString(),
                    'valid_to' => Carbon::now()->addMonths(rand(1, 3))->toDateString(),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
