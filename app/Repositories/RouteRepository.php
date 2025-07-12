<?php
namespace App\Repositories;

use App\Models\Route;
use App\Repositories\Contracts\RouteRepositoryInterface;

class RouteRepository implements RouteRepositoryInterface
{
    public function create(array $data)
    {
        return Route::create($data);
    }

    public function findByBookingId(int $bookingId)
    {
        return Route::where('booking_id', $bookingId)->first();
    }
}
