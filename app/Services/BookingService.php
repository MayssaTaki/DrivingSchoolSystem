<?php
namespace App\Services;

use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\CarRepositoryInterface;
use App\Repositories\Contracts\CarReservationRepositoryInterface;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Repositories\StudentRepository;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Services\Interfaces\BookingServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\EmailVerificationServiceInterface;
use App\Services\Interfaces\CarReservationServiceInterface;

use App\Events\SessionBooked;
use App\Events\SessionAutoBooked;
use App\Events\SessionStarted;
use App\Events\SessionCompleted;
use App\Events\SessionCancelled;
use App\Models\User;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class BookingService implements BookingServiceInterface
{ protected ActivityLoggerServiceInterface $activityLogger;
    protected LogServiceInterface $logService;
        protected StudentRepositoryInterface $studentRepo;

        protected TransactionService $transactionService;
        protected EmailVerificationServiceInterface $emailservice;
protected FirebaseServiceInterface $firebaseservice;
 protected   CarReservationServiceInterface $carReservationService;
 protected   CarReservationRepositoryInterface $carReservationRepo;



    public function __construct(
                EmailVerificationServiceInterface $emailService,
        protected BookingRepositoryInterface $bookingRepo,
        protected CarRepositoryInterface $carRepo,
        TransactionServiceInterface $transactionService,
        ActivityLoggerServiceInterface $activityLogger,
        LogServiceInterface $logService,
                StudentRepositoryInterface $studentRepo,
    CarReservationServiceInterface $carReservationService,
  CarReservationRepositoryInterface $carReservationRepo,
        protected TrainingSessionRepositoryInterface $sessionRepo,
                FirebaseService $firebaseService

    ) {
        $this->transactionService = $transactionService;
        $this->activityLogger = $activityLogger;
                $this->studentRepo = $studentRepo;
        $this->firebaseService = $firebaseService;

        $this->logService = $logService;
                $this->emailService=$emailService;
    $this->carReservationService = $carReservationService;
    $this->carReservationRepo = $carReservationRepo;


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

            $session = $this->sessionRepo->findWithLock($sessionId);
            $car = $this->carRepo->findWithLock($carId);

            $isAvailable = $this->carReservationService->checkAvailability(
                $carId,
                $session->session_date,
                $session->start_time,
                $session->end_time
            );

            if (!$isAvailable) {
                throw new \Exception('السيارة غير متاحة في هذا الوقت');
            }

            $booking = $this->bookingRepo->create([
                'student_id' => $studentId,
                'session_id' => $session->id,
                'trainer_id' => $session->trainer_id,
                'car_id' => $carId,
                'status' => 'booked',
            ]);

            $this->sessionRepo->updateStatus($session->id, 'booked');

            $this->carReservationService->createReservation([
                'car_id' => $carId,
                'session_id' => $session->id,
                'start_time' => Carbon::parse("{$session->session_date} {$session->start_time}"),
                'end_time' => Carbon::parse("{$session->session_date} {$session->end_time}"),
            ]);

            $booking->load('session.trainer.user');

            event(new SessionBooked($booking));

            $trainer = $booking->session->trainer;
            $user = $trainer->user;

            if ($user && $user->fcm_token) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    '📅 تم حجز جلسة تدريب جديدة',
                    "تم حجز جلسة تدريب بتاريخ {$booking->session->day_of_week} الساعة {$booking->session->start_time}."
                );
            }

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
                 $session->end_time,
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
$this->carReservationService->createReservation([
    'car_id' => $availableCar->id,
    'session_id' => $session->id,
    'start_time' => Carbon::parse("{$session->session_date} {$session->start_time}"),
    'end_time' => Carbon::parse("{$session->session_date} {$session->end_time}"),
]);
            $booking->load('session.trainer.user');

            event(new SessionAutoBooked($booking));

            $trainer = $booking->session->trainer;
            $user = $trainer->user;

            if ($user && $user->fcm_token) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    '⚙️ تم حجز جلسة تدريب تلقائيًا',
                    "تم حجز جلسة تدريب بتاريخ {$booking->session->day_of_week} الساعة {$booking->session->start_time} تلقائيًا."
                );
            }

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

$booking->load('session');
event(new SessionCompleted($booking));
$users= User::where('role', 'employee')
                ->whereNotNull('fcm_token')
                ->get();

            foreach ($users as $user) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                '✅ تم إنهاء جلسة تدريب',
                    "تم إنهاء جلسة تدريب بتاريخ {$booking->session->day_of_week} الساعة {$booking->session->start_time} من قبل المدرب : {$booking->trainer->first_name} {$booking->trainer->last_name}.",
                );
            }
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

                $this->bookingRepo->updateStatus($booking->id, 'started'); 
$booking->load('session'); 
event(new SessionStarted($booking));
$users= User::where('role', 'employee')
                ->whereNotNull('fcm_token')
                ->get();

            foreach ($users as $user) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                '🚀 تم بدء جلسة تدريب',
                    "تم بدء جلسة تدريب بتاريخ {$booking->session->day_of_week} الساعة {$booking->session->start_time} بواسطة المدرب : {$booking->trainer->first_name} {$booking->trainer->last_name}.",
                );
            }
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
    public function getTrainerBookedSessionsForAdmin(int $trainerId)
{
    return $this->bookingRepo->getBookedSessionsByTrainer($trainerId);
}

 public function getStudentBookedSessions(int $studentId)
    {
        return $this->bookingRepo->getBookedSessionsByStudent($studentId);
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
$this->carReservationRepo->deleteBySessionId($session->id);

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

            $currentUser = auth()->user();
            $isStudent = ($currentUser->role === 'student');

            $booking->load('student.user', 'session.trainer.user');

            event(new SessionCancelled($booking, $session, $isStudent));

            $recipient = $isStudent ? $booking->session->trainer?->user : $booking->student?->user;

            if ($recipient && $recipient->fcm_token) {
                $who = $isStudent ? 'الطالب' : 'المدرب';

                $this->firebaseService->sendNotification(
                    $recipient->fcm_token,
                    '⚠️ تم إلغاء جلسة تدريب',
                    "قام {$who} بإلغاء جلسة التدريب بتاريخ {$session->session_date} الساعة {$session->start_time}.",
                  
                );
            }

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
