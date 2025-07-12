<?php
namespace App\Services\Interfaces;

interface RouteServiceInterface
{
    public function defineRouteForBooking(int $bookingId, array $data);
}
