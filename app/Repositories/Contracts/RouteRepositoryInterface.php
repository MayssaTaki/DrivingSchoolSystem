<?php
namespace App\Repositories\Contracts;

interface RouteRepositoryInterface
{
    public function create(array $data);
    public function findByBookingId(int $bookingId);
}
