<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\TrainingSession;
use App\Models\Student;
use App\Models\Car;
use App\Models\CarReservation;
use App\Models\BookingStatusLog;
use Carbon\Carbon;
use App\Jobs\GenerateRouteForBooking;

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

        $sessions = TrainingSession::with('trainer')->get();

        if ($sessions->isEmpty()) {
            $this->command->warn('⚠️ لا توجد جلسات متاحة.');
            return;
        }

        $statuses = ['booked', 'cancelled', 'completed'];

        // 🚗 إحداثيات أحياء دمشق
        $damascusCoordinates = [
            [33.5138, 36.2765], // باب توما
            [33.5102, 36.2913], // المزة
            [33.5091, 36.3064], // أبو رمانة
            [33.5007, 36.3019], // البرامكة
            [33.4985, 36.2767], // الميدان
            [33.5123, 36.2731], // الصالحية
            [33.5260, 36.3156], // التجارة
            [33.5242, 36.2920], // المهاجرين
        ];

        foreach ($sessions as $session) {
            $studentId = $students[array_rand($students)];
            $status = $statuses[array_rand($statuses)];

            $start = Carbon::parse($session->session_date . ' ' . $session->start_time);
            $end = Carbon::parse($session->session_date . ' ' . $session->end_time);

            $availableCar = null;
            foreach ($cars as $carId) {
                $isReserved = CarReservation::where('car_id', $carId)
                    ->where(function ($q) use ($start, $end) {
                        $q->where('start_time', '<', $end)
                          ->where('end_time', '>', $start);
                    })->exists();

                if (!$isReserved) {
                    $availableCar = $carId;
                    break;
                }
            }

            if (!$availableCar) {
                $this->command->warn("🚫 لا توجد سيارة متاحة للجلسة رقم {$session->id} في هذا الوقت.");
                continue;
            }

            $booking = Booking::create([
                'student_id' => $studentId,
                'session_id' => $session->id,
                'trainer_id' => $session->trainer_id,
                'car_id'     => $availableCar,
                'status'     => $status,
            ]);

            $session->update(['status' => $status]);

            CarReservation::create([
                'car_id'     => $availableCar,
                'session_id' => $session->id,
                'start_time' => $start,
                'end_time'   => $end,
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'status'     => $status,
                'changed_at' => $start,
                'changed_by' => $session->trainer->user_id,
            ]);

            // 🚀 إرسال Job بدلاً من الاتصال المباشر
            if (in_array($status, ['booked', 'completed'])) {
                $startCoord = $damascusCoordinates[array_rand($damascusCoordinates)];
                $endCoord = $damascusCoordinates[array_rand($damascusCoordinates)];

                while ($startCoord === $endCoord) {
                    $endCoord = $damascusCoordinates[array_rand($damascusCoordinates)];
                }

                GenerateRouteForBooking::dispatch($booking->id, $startCoord, $endCoord)
                    ->delay(now()->addSeconds(rand(1, 20))); // تأخير عشوائي لتوزيع الضغط
            }
        }

        $this->command->info('✅ تم إنشاء الحجوزات + حجوزات السيارات + سجلات الحالة + إضافة Jobs للمسارات.');
    }
}
