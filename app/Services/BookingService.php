<?php
namespace App\Services;

use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\CarRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Repositories\StudentRepository;

use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{ protected ActivityLoggerService $activityLogger;
    protected LogService $logService;
        protected TransactionService $transactionService;
        protected EmailVreificationService $emailservice;



    public function __construct(
                EmailVerificationService $emailService,
        protected BookingRepositoryInterface $bookingRepo,
        protected CarRepositoryInterface $carRepo,
        TransactionService $transactionService,
        ActivityLoggerService $activityLogger,
        LogService $logService,
                StudentRepository $studentRepo,

        protected TrainingSessionRepositoryInterface $sessionRepo
    ) {
        $this->transactionService = $transactionService;
        $this->activityLogger = $activityLogger;
                $this->studentRepo = $studentRepo;

        $this->logService = $logService;
                $this->emailService=$emailService;


    }

    protected function ensureSessionIsAvailable(int $sessionId)
{
    if (!$this->bookingRepo->isSessionAvailable($sessionId)) {
        throw ValidationException::withMessages([
            'session' => 'الجلسة غير متاحة للحجز.',
        ]);
    }
}
    protected function ensureSessionIsBook(int $sessionId)
{
    if (!$this->bookingRepo->isSessionBook($sessionId)) {
        throw ValidationException::withMessages([
            'session' => 'الجلسة غير محجوزة.',
        ]);
    }
}

protected function ensureCarIsBook(int $carId)
{
    if (!$this->carRepo->isCarBook($carId)) {
        throw ValidationException::withMessages([
            'car' => 'السيارة غير محجوزة .',
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

public function autoBookSession(int $studentId, int $sessionId, string $transmission, bool $isForSpecialNeeds)
{
    try {
        return $this->transactionService->run(function () use ($studentId, $sessionId, $transmission, $isForSpecialNeeds) {
            $this->ensureSessionIsAvailable($sessionId);

            $session = $this->sessionRepo->findWithLock($sessionId);

            $availableCar = $this->carRepo->getFirstAvailableForSession(
                $session->session_date,
                $session->start_time,
                $transmission,
                $isForSpecialNeeds
            );

            if (!$availableCar) {
                throw new \Exception('لا توجد سيارات متاحة بالمواصفات المطلوبة في هذا الوقت.');
            }

            $booking = $this->bookingRepo->create([
                'student_id' => $studentId,
                'session_id' => $session->id,
                'trainer_id' => $session->trainer_id,
                'car_id' => $availableCar->id,
                'status' => 'booked',
            ]);

            $this->sessionRepo->updateStatus($session->id, 'booked');
            $this->carRepo->updateStatus($availableCar->id, 'booked');

            $this->activityLogger->log(
                'تم حجز جلسة تدريب تلقائيًا',
                [
                    'student_id' => $studentId,
                    'session_id' => $sessionId,
                    'car_id' => $availableCar->id,
                    'session_date' => $session->session_date,
                    'start_time' => $session->start_time,
                ],
                'bookings',
                $booking,
                auth()->user(),
                'auto-book'
            );

            return $booking;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل الحجز التلقائي للجلسة التدريبية', [
            'message' => $e->getMessage(),
            'session_id' => $sessionId,
            'student_id' => $studentId,
            'trace' => $e->getTraceAsString(),
        ], 'bookings');

        throw $e;
    }
}








protected function ensureBookingIsStarted($booking)
{
    if ($booking->status !== 'started') {
        throw ValidationException::withMessages([
            'booking' => 'لا يمكن إنهاء جلسة غير مبتدئة.',
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

           $this->ensureBookingIsStarted($booking);
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





 public function startSession(int $bookingId)
    {
        try {
        return $this->transactionService->run(function () use ($bookingId) {
                $booking = $this->bookingRepo->findWithRelations($bookingId, ['session', 'car']);
                
                if (Gate::denies('start', $booking)) {
                    throw new AuthorizationException('ليس لديك صلاحية بدء الجلسة.');
                }

                if (!in_array($booking->status, ['booked'])) {
                    throw new \Exception('لا يمكن بدء جلسة غير محجوزة أو مكتملة.');
                }

                $this->bookingRepo->updateStatus($booking->id, 'started'); // أو حالة خاصة لو تريدها مثل "started"

                $this->activityLogger->log(
                    'بدء جلسة تدريب',
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
                    'start'
                );
            });
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل في بدء الجلسة', [
                'message'    => $e->getMessage(),
                'booking_id' => $bookingId,
            ], 'bookings');

            throw $e;
        }
    }






  public function getTrainerBookedSessions(int $trainerId)
    {
        return $this->bookingRepo->getBookedSessionsByTrainer($trainerId);
    }


   public function CancelSession(int $bookingId)
{
    try {
        return $this->transactionService->run(function () use ($bookingId) {
            $booking = $this->bookingRepo->getBySessionIdWithLock($bookingId);

            $session = $this->sessionRepo->findWithLock($booking->session_id);
            $car = $this->carRepo->findWithLock($booking->car_id);

            $this->ensureSessionIsBook($session->id);
            $this->ensureCarIsBook($car->id);

            $this->bookingRepo->updateStatus($booking->id, 'cancelled');
            $this->sessionRepo->updateStatus($session->id, 'cancelled');
            $this->carRepo->updateStatus($car->id, 'available');

            $this->activityLogger->log(
                'الغاء جلسة تدريب',
                [
                    'student_id' => $booking->student_id,
                    'session_day' => $session->day_of_week ?? null,
                    'session_time' => $session->start_time ?? null,
                    'car_id' => $car->id,
                ],
                'bookings',
                $booking,
                auth()->user(),
                'book'
            );
            $this->sendSessionCancellationEmail($booking, $session);

            return $booking;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في الغاء الجلسة التدريبية', [
            'message' => $e->getMessage(),
            'booking_id' => $bookingId,
            'trace' => $e->getTraceAsString(),
        ], 'bookings');

        throw $e;
    }
}
protected function sendSessionCancellationEmail($booking, $session)
{
    $currentUser = auth()->user();
$isStudent = ($currentUser->role === 'student');

    $recipientUser = $isStudent
        ? $session->trainer->user
        : $booking->student->user;

   $message = $isStudent
    ? 'قام الطالب بإلغاء جلسة التدريب المحددة. .'
    : 'قام المدرب بإلغاء جلسة التدريب المحددة. يمكنك الآن حجز جلسة جديدة في الوقت المناسب لك.';


    $htmlContent = "
        <p>{$message}</p>
        <p>اليوم: <strong>{$session->session_date}</strong></p>
        <p>الوقت: <strong>{$session->start_time}</strong></p>
            <p>شكراً لاستخدامك نظامنا.</p>

    ";

    $this->emailService->sendCustomEmail($recipientUser, 'إلغاء جلسة تدريب', $htmlContent);
}

}
