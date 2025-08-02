<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Car;
use App\Models\CarLocation;
use Carbon\Carbon;

class CarLocationSeeder extends Seeder
{
    public function run(): void
    {
        $cars = Car::where('status', 'available')->get();

        if ($cars->isEmpty()) {
            $this->command->warn('⚠️ لا توجد سيارات متاحة (available) لتوليد المواقع.');
            return;
        }

        $damascusCoordinates = [
            [33.5138, 36.2765], // باب توما
            [33.5102, 36.2913], // المزة
            [33.5091, 36.3064], // أبو رمانة
            [33.5007, 36.3019], // البرامكة
            [33.4985, 36.2767], // الميدان
            [33.5123, 36.2731], // الصالحية
        ];

        foreach ($cars as $car) {
            $startTime = Carbon::now()->subMinutes(30);

            for ($i = 0; $i < 10; $i++) {
                $coord = $damascusCoordinates[array_rand($damascusCoordinates)];
                CarLocation::create([
                    'car_id' => $car->id,
                    'latitude' => $coord[0] + rand(-50, 50) / 10000,
                    'longitude' => $coord[1] + rand(-50, 50) / 10000,
                    'recorded_at' => $startTime->copy()->addMinutes($i * 3),
                ]);
            }
        }

        $this->command->info('✅ تم توليد بيانات تتبع لـ سيارات متاحة فقط من دمشق.');
    }
}
