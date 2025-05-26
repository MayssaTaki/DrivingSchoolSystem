<?php
namespace App\Repositories\Contracts;

interface BookingRepositoryInterface
{
    public function create(array $data);
        public function getBookedSessionsByTrainer(int $trainerId);

           public function updateStatus(int $bookId, string $status): bool;
        public function findWithRelations(int $id, array $relations = []);
    public function isSessionAvailable(int $sessionId): bool;
}
