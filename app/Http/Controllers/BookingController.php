<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\AutoBookRequest;
use Illuminate\Validation\ValidationException;
use App\Exceptions\CarUnavailableException;

use App\Services\Interfaces\BookingServiceInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BookingResource;
use Illuminate\Http\Request;
use App\Exceptions\InvalidAmountException;

class BookingController extends Controller
{
    public function __construct(protected BookingServiceInterface $bookingService) {}

public function store(BookingRequest $request): JsonResponse
{
    $studentId = auth()->user()->student->id;
    $sessionId = $request->input('session_id');
    $carId = $request->input('car_id');
    $amount = $request->input('amount');

    try {
        $booking = $this->bookingService->bookSession($studentId, $sessionId, $carId, $amount);

        return response()->json([
            'message' => 'تم حجز الجلسة بنجاح.',
            'data' => [
                'booking' => new BookingResource($booking['booking']),
                'payment' => $booking['payment']
            ]
        ], 201);

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'خطأ في التحقق.',
            'errors' => $e->errors(),
        ], 422);

    } catch (CarUnavailableException $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 409);

    } catch (InvalidAmountException $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 400); 

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ غير متوقع.',
            'error' => $e->getMessage(),
        ], 500);
    }
}





public function autoBook(AutoBookRequest $request)
{
    $studentId = auth()->user()->student->id;

    try {
        $booking = $this->bookingService->autoBookSession(
            $studentId,
            $request->input('session_id'),
            $request->input('transmission'),
            $request->boolean('is_for_special_needs') ,
             $request->input('amount') 

        );

        return response()->json([
            'message' => 'تم الحجز بنجاح',
     'data' => [
                'booking' => new BookingResource($booking['booking']),
                'payment' => $booking['payment']
            ]        ], 201);

    } catch (CarUnavailableException $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 409); 

    } catch (InvalidAmountException $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 400); 

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ غير متوقع.',
            'error' => $e->getMessage(),
        ], 500);
    }
}






 public function startSession(Request $request, $id)
    {
        try {
            $this->bookingService->startSession($id);
            return response()->json(['message' => 'تم بدء الجلسة بنجاح.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
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

    public function getTrainerBookedSessionsForAdmin( int $trainerId)
{

    $bookings = $this->bookingService->getTrainerBookedSessionsForAdmin($trainerId);

    return BookingResource::collection($bookings);
}

      public function getStudentBookedSessions(Request $request)
    {
        $studentId = $request->user()->student->id; 

        $bookings = $this->bookingService->getStudentBookedSessions($studentId);

        return BookingResource::collection($bookings);
    }


}





