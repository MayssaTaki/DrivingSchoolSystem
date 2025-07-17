<?php
namespace App\Services\Interfaces;

interface MapServiceInterface
{
    public function getRouteData(float $startLat, float $startLng, float $endLat, float $endLng): array;
}
