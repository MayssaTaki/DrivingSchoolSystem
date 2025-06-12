<?php
namespace App\Exceptions;

use Exception;



class BookingAlreadyFeedbackException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'تم بالفعل تقييم هذا الحجز.',
        ], 400);
    }
}
