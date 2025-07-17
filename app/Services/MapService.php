<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\Interfaces\MapServiceInterface;

class MapService implements MapServiceInterface
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openrouteservice.key');
    }

    public function getRouteData(float $startLat, float $startLng, float $endLat, float $endLng): array
    {
        // 1. Directions API
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openrouteservice.org/v2/directions/driving-car', [
            'coordinates' => [
                [$startLng, $startLat],
                [$endLng, $endLat]
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('فشل جلب بيانات المسار.');
        }

        $data = $response->json();
        $route = $data['routes'][0];

        $distance = $route['summary']['distance'];
        $duration = $route['summary']['duration'];
        $polyline = $route['geometry'];

        // 2. Reverse geocode
        $startAddress = $this->reverseGeocode($startLat, $startLng);
        $endAddress = $this->reverseGeocode($endLat, $endLng);

        return [
            'polyline' => $polyline,
            'distance' => $distance,
            'duration' => $duration,
            'start_address' => $startAddress,
            'end_address' => $endAddress,
        ];
    }

    protected function reverseGeocode(float $lat, float $lng): ?string
    {
        $res = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])->get('https://api.openrouteservice.org/geocode/reverse', [
            'point.lat' => $lat,
            'point.lon' => $lng,
        ]);

        if (!$res->successful()) return null;

        return $res->json()['features'][0]['properties']['label'] ?? null;
    }
}
