<?php
namespace App\Repositories;

use App\Models\TrainingSession;
use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;

class BookingRepository implements BookingRepositoryInterface
{

        protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }
    public function create(array $data)
    {
        return Booking::create($data);
    }

  public function getBookedSessionsByTrainer(int $trainerId)
    {
        return $this->booking
            ->where('trainer_id', $trainerId)
            ->where('status', 'booked')
            ->with(['session', 'student', 'car'])  
            ->get();
    }
    
    public function isSessionAvailable(int $sessionId): bool
    {
        $session = TrainingSession::find($sessionId);
        return $session && $session->status === 'available';
    }


 public function isSessionBook(int $sessionId): bool
    {
        $session = TrainingSession::find($sessionId);
        return $session && $session->status === 'booked';
    }

       public function updateStatus(int $bookId, string $status): bool
{
    return Booking::where('id', $bookId)
        ->update(['status' => $status]);
}
    public function findWithRelations(int $id, array $relations = [])
{
    return Booking::with($relations)->findOrFail($id);
}
  public function getBySessionIdWithLock(int $sessionId): ?Booking
{
    return Booking::where('session_id', $sessionId)->lockForUpdate()->first();
}

}
