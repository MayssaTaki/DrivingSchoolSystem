<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingSession;
use App\Models\TrainingSchedule;
use Carbon\Carbon;

class TrainingSessionSeeder extends Seeder
{
    public function run()
    {
        $schedules = TrainingSchedule::with('trainer')->get();

        if ($schedules->isEmpty()) {
            $this->command->warn('⚠️ لا يوجد جداول تدريب، الرجاء تشغيل TrainingScheduleSeeder أولاً.');
            return;
        }

        $statuses = ['available', 'booked', 'vacation', 'cancelled', 'completed'];

        foreach ($schedules as $schedule) {
            $startDate = Carbon::parse($schedule->valid_from);
            $endDate = Carbon::parse($schedule->valid_to);

            $date = $startDate->copy()->next($schedule->day_of_week);

            while ($date <= $endDate) {
                $exists = TrainingSession::where('trainer_id', $schedule->trainer_id)
                    ->where('session_date', $date->toDateString())
                    ->where('start_time', $schedule->start_time)
                    ->exists();

                if (!$exists) {
                    TrainingSession::create([
                        'trainer_id'   => $schedule->trainer_id,
                        'schedule_id'  => $schedule->id,
                        'session_date' => $date->toDateString(),
                        'start_time'   => $schedule->start_time,
                        'end_time'     => $schedule->end_time,
                        'status'       => $statuses[array_rand($statuses)],
                    ]);
                }

                $date->addWeek(); 
            }
        }
    }
}
