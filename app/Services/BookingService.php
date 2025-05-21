<?php
namespace App\Services;

use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        protected BookingRepositoryInterface $bookingRepo,
        protected TrainingSessionRepositoryInterface $sessionRepo
    ) {}//////////////////////testttttttt

    public function bookSession(int $studentId, int $sessionId, int $carId): \App\Models\Booking
    {

        if (!$this->bookingRepo->isSessionAvailable($sessionId)) {
            throw ValidationException::withMessages([
                'session' => 'الجلسة غير متاحة للحجز.',
            ]);
        }

        $session = $this->sessionRepo->find($sessionId);

        return DB::transaction(function () use ($studentId, $session, $carId) {
            $booking = $this->bookingRepo->create([
                'student_id' => $studentId,
                'session_id' => $session->id,
                'trainer_id' => $session->trainer_id, 
                'car_id' => $carId,
                'status' => 'booked',
            ]);

            $this->sessionRepo->updateStatus($session->id, 'booked');

            return $booking;
        });
    }
}
