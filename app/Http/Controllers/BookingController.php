<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BookingResource;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function store(BookingRequest $request): JsonResponse
    {
$studentId = auth()->user()->student->id;
        $sessionId = $request->input('session_id');
        $carId = $request->input('car_id');

        $booking = $this->bookingService->bookSession($studentId, $sessionId, $carId);

        return response()->json([
            'message' => 'تم حجز الجلسة بنجاح.',
            'data' =>  new BookingResource($booking),

        ], 201);
    }


public function getRecommendedSessions(Request $request)
{
    $validated = $request->validate([
        'student_id' => 'required|integer|exists:students,id',
        'preferred_date' => 'required|date',
        'preferred_time' => 'required|date_format:H:i',
    ]);

    $sessions = $this->bookingService->getRecommendedSessions(
        $validated['student_id'],
        $validated['preferred_date'],
        $validated['preferred_time']
    );

    return response()->json([
        'sessions' => $sessions,
    ]);
}




 public function autoBook(Request $request)
{
    $validated = $request->validate([
        'student_id' => 'required|integer|exists:students,id',
        'preferred_date' => 'required|date',
        'preferred_time' => 'required|date_format:H:i',
    ]);

    $booking = $this->bookingService->autoBookSession(
        $validated['student_id'],
        $validated['preferred_date'],
        $validated['preferred_time']
    );

    return response()->json([
        'message' => 'تم الحجز بنجاح',
        'booking' => $booking,
    ]);
}














public function complete($bookingId)
{
    try {
        $this->bookingService->completeSession((int) $bookingId);

        return response()->json([
            'status' => true,
            'message' => 'تم إنهاء الجلسة بنجاح. .',
            'data' => null
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء إنهاء الجلسة.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function cancell($bookingId)
{
    try {
        $this->bookingService->CancelSession((int) $bookingId);

        return response()->json([
            'status' => true,
            'message' => 'تم الغاء  الجلسة بنجاح. .',
            'data' => null
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء الغاء الجلسة.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function getTrainerBookedSessions(Request $request)
    {
        $trainerId = $request->user()->trainer->id; 

        $bookings = $this->bookingService->getTrainerBookedSessions($trainerId);

        return BookingResource::collection($bookings);
    }


}
