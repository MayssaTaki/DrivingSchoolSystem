<?php
namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\ScheduleException;
use App\Models\TrainingSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class ScheduleExceptionSeeder extends Seeder
{
    public function run()
    {
        $trainers = Trainer::all();

        if ($trainers->isEmpty()) {
            $this->command->warn('⚠️ لا يوجد مدربين في قاعدة البيانات.');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected'];
        $reasons = [
            'ظروف عائلية طارئة',
            'إجازة طبية',
            'موعد رسمي مهم',
            'مناسبة خاصة',
            'طلب إجازة اعتيادية'
        ];

        foreach ($trainers as $trainer) {
            $date = Carbon::now()->addDays(rand(2, 10))->toDateString();
            $status = Arr::random($statuses);

            $exception = ScheduleException::create([
                'trainer_id'     => $trainer->id,
                'exception_date' => $date,
                'reason'         => Arr::random($reasons),
                'status'         => $status,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            if ($status === 'approved') {
                TrainingSession::where('trainer_id', $trainer->id)
                    ->where('session_date', $date)
                    ->update(['status' => 'vacation']);
            }
        }
    }
}
