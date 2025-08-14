<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Car;
use App\Models\CarLocation;
use App\Models\TrainingSession;
use App\Models\Route;
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

        // جلب جميع الجلسات المحجوزة أو المكتملة مع المسار
        $sessions = TrainingSession::whereIn('status', ['booked', 'completed'])
            ->whereHas('bookings.route') // فقط الجلسات التي لها مسار
            ->with(['bookings.route'])
            ->get();

        if ($sessions->isEmpty()) {
            $this->command->warn('⚠️ لا توجد جلسات محجوزة أو مكتملة لها مسار.');
            return;
        }

        foreach ($cars as $car) {
            foreach ($sessions as $session) {
                $route = $session->booking?->route;

                if (!$route || !$route->polyline) {
                    continue; // لا يوجد مسار لهذه الجلسة
                }

                // فك polyline إلى نقاط GPS
                $points = $this->decodePolyline($route->polyline);

                if (empty($points)) {
                    continue;
                }

                $startTime = Carbon::parse($session->start_time);

                // توزيع 10 نقاط من المسار
                $selectedPoints = $this->pickPointsFromRoute($points, 10);

                foreach ($selectedPoints as $index => $point) {
                    CarLocation::create([
                        'car_id' => $car->id,
                        'session_id' => $session->id,
                        'latitude' => $point[0],
                        'longitude' => $point[1],
                        'recorded_at' => $startTime->copy()->addMinutes($index * 3),
                    ]);
                }
            }
        }

        $this->command->info('✅ تم توليد نقاط تتبع حقيقية متطابقة مع مسار الجلسات.');
    }

    /**
     * فك تشفير Google/ORS polyline إلى مصفوفة إحداثيات
     */
    private function decodePolyline($encoded)
    {
        $points = [];
        $index = $lat = $lng = 0;
        $len = strlen($encoded);

        while ($index < $len) {
            $b = $shift = $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $shift = $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = [$lat / 1E5, $lng / 1E5];
        }
        return $points;
    }

    /**
     * أخذ عدد محدد من النقاط موزعة من المسار
     */
    private function pickPointsFromRoute(array $points, int $count)
    {
        if (count($points) <= $count) {
            return $points;
        }

        $step = floor(count($points) / $count);
        $selected = [];

        for ($i = 0; $i < count($points); $i += $step) {
            $selected[] = $points[$i];
            if (count($selected) >= $count) break;
        }

        return $selected;
    }
}
