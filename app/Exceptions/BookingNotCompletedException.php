<?php
namespace App\Exceptions;

use Exception;

class BookingNotCompletedException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'لا يمكن تقييم الطالب إلا بعد انتهاء الجلسة.',
        ], 400);
    }
}


