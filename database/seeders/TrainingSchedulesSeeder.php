<?php

namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\TrainingSchedule;
use App\Models\ScheduleException;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TrainingSchedulesSeeder extends Seeder
{
    public function run()
    {
        $trainers = Trainer::all();
        
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'saturday'];
        
        foreach ($trainers as $trainer) {
            // إنشاء جدول تدريب لكل مدرب (3-5 أيام لكل مدرب)
            $daysCount = rand(3, 5);
            $selectedDays = array_rand(array_flip($daysOfWeek), $daysCount);
            
            foreach ($selectedDays as $day) {
                $startHour = rand(8, 16); // بين 8 صباحاً و4 مساءً
                $startTime = sprintf("%02d:00:00", $startHour);
                $endTime = sprintf("%02d:00:00", $startHour + 2);
                
                TrainingSchedule::create([
                    'trainer_id' => $trainer->id,
                    'day_of_week' => $day,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_recurring' => true,
                    'valid_from' => Carbon::now()->subMonths(2),
                    'valid_to' => Carbon::now()->addMonths(6),
                    'status' => 'inactive'
                ]);
            }
            
            // داخل الحلقة:
$exceptionDate = Carbon::now()->addDays(rand(1, 30))->toDateString();

$exists = ScheduleException::where('trainer_id', $trainer->id)
    ->whereDate('exception_date', $exceptionDate)
    ->exists();

if (!$exists) {
    ScheduleException::create([
        'trainer_id' => $trainer->id,
        'exception_date' => $exceptionDate,
        'is_available' => rand(0, 1),
        'available_start_time' => rand(0, 1) ? '09:00:00' : null,
        'available_end_time' => rand(0, 1) ? '11:00:00' : null,
        'reason' => rand(0, 1) ? 'إجازة رسمية' : 'تدريب خارجي'
    ]);
}}}}

        