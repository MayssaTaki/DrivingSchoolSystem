<?php
namespace App\Repositories;

use App\Models\TrainingSession;
use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $data)
    {
        return Booking::create($data);
    }


    
    public function isSessionAvailable(int $sessionId): bool
    {
        $session = TrainingSession::find($sessionId);
        return $session && $session->status === 'available';
    }
}
