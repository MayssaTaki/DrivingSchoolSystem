<?php

namespace App\Services\Interfaces;

interface BookingServiceInterface
{
    public function bookSession(int $studentId, int $sessionId, int $carId,int $amount);

    public function autoBookSession(int $studentId, int $sessionId, string $transmission, bool $isForSpecialNeeds,int $amount);

    public function completeSession(int $bookingId);

    public function startSession(int $bookingId);

    public function getTrainerBookedSessions(int $trainerId);

    public function getStudentBookedSessions(int $studentId);

    public function cancelSession(int $bookingId);
}
