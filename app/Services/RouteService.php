<?php
namespace App\Services;

use App\Repositories\Contracts\RouteRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\FirebaseServiceInterface;
use App\Services\Interfaces\LogServiceInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Services\Interfaces\RouteServiceInterface;
use App\Services\Interfaces\MapServiceInterface;

class RouteService implements RouteServiceInterface
{
    public function __construct(
        protected RouteRepositoryInterface $routeRepo,
        protected BookingRepositoryInterface $bookingRepo,
        protected TransactionServiceInterface $transactionService,
        protected ActivityLoggerServiceInterface $activityLogger,
        protected LogServiceInterface $logService,
        protected FirebaseServiceInterface $firebaseService,
            protected MapServiceInterface $mapService,


    ) {
                $this->firebaseService = $firebaseService;
                 $this->mapService = $mapService;

    }

  public function defineRouteForBooking(int $bookingId, array $data)
{
    try {
        return $this->transactionService->run(function () use ($bookingId, $data) {
            $booking = $this->bookingRepo->findById($bookingId);

            if (Gate::denies('defineRoute', $booking)) {
                throw new AuthorizationException('ليس لديك صلاحية تحديد مسار لهذه الجلسة.');
            }

            if ($booking->status !== 'booked') {
                throw new \Exception('يمكن تحديد المسار فقط للجلسات المحجوزة.');
            }

            $routeExists = $this->routeRepo->findByBookingId($booking->id);

            if ($routeExists) {
                throw new \Exception('تم تحديد المسار من قبل لهذا الحجز.');
            }
$response = $this->mapService->getRouteData(
    $data['start_lat'], $data['start_lng'],
    $data['end_lat'], $data['end_lng']
);
            $route = $this->routeRepo->create([
                'booking_id' => $booking->id,
                'start_lat' => $data['start_lat'],
                'start_lng' => $data['start_lng'],
                'end_lat' => $data['end_lat'],
                'end_lng' => $data['end_lng'],
                'polyline' => $response['polyline'],
    'distance_in_meters' => $response['distance'],
    'duration_in_seconds' => $response['duration'],
    'start_address' => $response['start_address'],
    'end_address' => $response['end_address'],
            ]);
$route->load('booking.session');

            event(new \App\Events\RouteDefined($route));

            $student = $booking->student;
            $user = $student?->user;

            if ($user && $user->fcm_token) {
                $title = '📍 تم تحديد مسار التدريب';
                $body = "تم تحديد مسار التدريب لجلسة بتاريخ {$booking->session->session_date} الساعة {$booking->session->start_time}.";

                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    $title,
                    $body
                );

                logger('📣 تم إرسال إشعار FCM للطالب:', [
                    'user_id' => $user->id,
                    'booking_id' => $booking->id,
                    'fcm_token_exists' => !empty($user->fcm_token),
                ]);
            } else {
                logger("⛔ لم يتم العثور على المستخدم أو لا يوجد fcm_token للطالب ID: {$student?->id}");
            }

            $this->activityLogger->log(
                'تحديد مسار للجلسة',
                [
                    'booking_id' => $booking->id,
                    'start_lat' => $data['start_lat'],
                    'start_lng' => $data['start_lng'],
                    'end_lat' => $data['end_lat'],
                    'end_lng' => $data['end_lng'],
                ],
                'routes',
                $route,
                auth()->user(),
                'define-route'
            );

            return $route;
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل في تحديد المسار', [
            'message' => $e->getMessage(),
            'booking_id' => $bookingId,
            'trace' => $e->getTraceAsString(),
        ], 'routes');

        throw $e;
    }
}

}
