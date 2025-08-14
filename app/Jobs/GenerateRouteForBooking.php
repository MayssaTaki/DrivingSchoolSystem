<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Route;
use App\Models\CarLocation;
use App\Services\MapService;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateRouteForBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bookingId;
    protected $startCoord;
    protected $endCoord;

    public function __construct($bookingId, array $startCoord, array $endCoord)
    {
        $this->bookingId = $bookingId;
        $this->startCoord = $startCoord;
        $this->endCoord = $endCoord;
    }

    public function handle(MapService $mapService)
    {
        $booking = Booking::find($this->bookingId);

        if (!$booking || in_array($booking->status, ['cancelled', 'started'])) {
            return;
        }

        try {
            // إنشاء المسار
            $routeData = $mapService->getRouteData(
                $this->startCoord[0], $this->startCoord[1],
                $this->endCoord[0], $this->endCoord[1]
            );

            $route = Route::create([
                'booking_id'         => $booking->id,
                'start_lat'          => $this->startCoord[0],
                'start_lng'          => $this->startCoord[1],
                'end_lat'            => $this->endCoord[0],
                'end_lng'            => $this->endCoord[1],
                'polyline'           => $routeData['polyline'],
                'distance_in_meters' => $routeData['distance'],
                'duration_in_seconds'=> $routeData['duration'],
                'start_address'      => $routeData['start_address'],
                'end_address'        => $routeData['end_address'],
            ]);

            $this->generateCarLocations($booking, $route);

        } catch (Exception $e) {
            \Log::error("فشل جلب بيانات المسار للحجز {$this->bookingId}: " . $e->getMessage());
        }
    }

    private function generateCarLocations($booking, $route)
    {
        if (!$route->polyline) {
            return;
        }

        $points = $this->decodePolyline($route->polyline);
        if (empty($points)) {
            return;
        }

        $startTime = Carbon::parse($booking->session->start_time);
        $selectedPoints = $this->pickPointsFromRoute($points, 10);

        foreach ($selectedPoints as $index => $point) {
            CarLocation::create([
                'car_id'     => $booking->car_id,
                'session_id' => $booking->session_id,
                'latitude'   => $point[0],
                'longitude'  => $point[1],
                'recorded_at'=> $startTime->copy()->addMinutes($index * 3),
            ]);
        }
    }

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
