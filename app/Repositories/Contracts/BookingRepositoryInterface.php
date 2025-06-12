<?php
namespace App\Repositories\Contracts;
use App\Models\Booking;
interface BookingRepositoryInterface
{
    public function create(array $data);
  public function getBySessionIdWithLock(int $sessionId): ?Booking;      
    public function getBookedSessionsByTrainer(int $trainerId);
 public function isSessionBook(int $sessionId): bool;
      public function getBookedSessionsByStudent(int $studentId);
           public function updateStatus(int $bookId, string $status): bool;
        public function findWithRelations(int $id, array $relations = []);
    public function isSessionAvailable(int $sessionId): bool;
}
