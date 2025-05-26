<?php
namespace App\Services;

use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\CarRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;

use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{ protected ActivityLoggerService $activityLogger;
    protected LogService $logService;
        protected TransactionService $transactionService;

    public function __construct(
        protected BookingRepositoryInterface $bookingRepo,
        protected CarRepositoryInterface $carRepo,
        TransactionService $transactionService,
        ActivityLoggerService $activityLogger,
        LogService $logService,
        protected TrainingSessionRepositoryInterface $sessionRepo
    ) {
        $this->transactionService = $transactionService;
        $this->activityLogger = $activityLogger;
        $this->logService = $logService;
    }

    protected function ensureSessionIsAvailable(int $sessionId)
{
    if (!$this->bookingRepo->isSessionAvailable($sessionId)) {
        throw ValidationException::withMessages([
            'session' => 'الجلسة غير متاحة للحجز.',
        ]);
    }
}

protected function ensureCarIsAvailable(int $carId)
{
    if (!$this->carRepo->isCarAvailable($carId)) {
        throw ValidationException::withMessages([
            'car' => 'السيارة غير متاحة للحجز.',
        ]);
    }
}

 

 public function bookSession(int $studentId, int $sessionId, int $carId)
{
    try {
        return $this->transactionService->run(function () use ($studentId, $sessionId, $carId) {
             $this->ensureSessionIsAvailable($sessionId);
            $this->ensureCarIsAvailable($carId);

            $session = $this->sessionRepo->findWithLock($sessionId);
$car = $this->carRepo->findWithLock($carId);


            $booking = $this->bookingRepo->create([
                'student_id' => $studentId,
                'session_id' => $session->id,
                'trainer_id' => $session->trainer_id, 
                'car_id' => $carId,
                'status' => 'booked',
            ]);

            $this->sessionRepo->updateStatus($session->id, 'booked');
            $this->carRepo->updateStatus($car->id, 'booked');

            $this->activityLogger->log(
                'حجز جلسة تدريب',
                [
                    'student_id' => $studentId,
                    'session_day' => $session->day_of_week ?? null,
                    'session_time' => $session->start_time ?? null,
                    'car_id' => $carId,
                ],
                'bookings',
                $booking,
                auth()->user(),
                'book'
            );

            return $booking;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في حجز الجلسة التدريبية', [
            'message' => $e->getMessage(),
            'session_id' => $sessionId,
            'car_id' => $carId,
            'student_id' => $studentId,
            'trace' => $e->getTraceAsString(),
        ], 'bookings');

        throw $e;
    }
}
protected function ensureBookingIsBookable($booking)
{
    if ($booking->status !== 'booked') {
        throw ValidationException::withMessages([
            'booking' => 'لا يمكن إنهاء جلسة غير محجوزة.',
        ]);
    }
}


public function completeSession(int $bookingId)
{
    try {
        return $this->transactionService->run(function () use ($bookingId) {
            $booking = $this->bookingRepo->findWithRelations($bookingId, ['session', 'car']);
             if (Gate::denies('complete', $booking)) {
                throw new AuthorizationException('ليس لديك صلاحية انهاء الجلسة .');
            }

           $this->ensureBookingIsBookable($booking);
            $this->bookingRepo->updateStatus($booking->id, 'completed');
            $this->sessionRepo->updateStatus($booking->session_id, 'completed');
            $this->carRepo->updateStatus($booking->car_id, 'available');


            $this->activityLogger->log(
                'إنهاء جلسة تدريب',
                [
                    'student_id'   => $booking->student_id,
                    'trainer_id'   => $booking->trainer_id,
                    'session_day'  => $booking->session->day_of_week ?? null,
                    'session_time' => $booking->session->start_time ?? null,
                    'car_id'       => $booking->car_id,
                ],
                'bookings',
                $booking,
                auth()->user(),
                'complete'
            );

        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في إنهاء الجلسة', [
            'message'     => $e->getMessage(),
            'booking_id'  => $bookingId,
        ], 'bookings');

        throw $e;
    }
}
  public function getTrainerBookedSessions(int $trainerId)
    {
        return $this->bookingRepo->getBookedSessionsByTrainer($trainerId);
    }


}
