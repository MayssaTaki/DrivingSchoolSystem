<?php
namespace App\Services;

use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\CarRepositoryInterface;

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

 public function bookSession(int $studentId, int $sessionId, int $carId)
{
    try {
        return $this->transactionService->run(function () use ($studentId, $sessionId, $carId) {
            if (!$this->bookingRepo->isSessionAvailable($sessionId)) {
                throw ValidationException::withMessages([
                    'session' => 'الجلسة غير متاحة للحجز.',
                ]);
            }

            if (!$this->carRepo->isCarAvailable($carId)) {
                throw ValidationException::withMessages([
                    'car' => 'السيارة غير متاحة للحجز.',
                ]);
            }

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


}
