<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\TrainingSession;
use App\Models\Student;
use App\Models\Car;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::pluck('id')->toArray();
        $cars = Car::where('status', 'available')->pluck('id')->toArray();

        if (empty($students) || empty($cars)) {
            $this->command->warn('⚠️ لا يوجد طلاب أو سيارات متاحة.');
            return;
        }

        $sessions = TrainingSession::whereNotIn('status', ['booked', 'started'])
            ->with('trainer')
            ->take(20)
            ->get();

        if ($sessions->isEmpty()) {
            $this->command->warn('⚠️ لا توجد جلسات متاحة للحجز.');
            return;
        }

        $statuses = ['booked', 'cancelled', 'completed'];

        foreach ($sessions as $session) {
            $studentId = $students[array_rand($students)];
            $carId = $cars[array_rand($cars)];
            $status = $statuses[array_rand($statuses)];

            Booking::create([
                'student_id' => $studentId,
                'session_id' => $session->id,
                'trainer_id' => $session->trainer_id,
                'car_id'     => $carId,
                'status'     => $status,
            ]);

            $session->update(['status' => $status]);

            if (in_array($status, ['booked', 'started'])) {
                \App\Models\Car::where('id', $carId)->update(['status' => 'booked']);
            }
        }

        $this->command->info('✅ تم إنشاء الحجوزات بنجاح.');
    }
}
