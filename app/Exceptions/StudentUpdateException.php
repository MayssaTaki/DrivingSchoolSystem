<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class StudentUpdateException extends Exception
{
    protected int $status;

    
    public function __construct(string $message = "فشل في تحديث بيانات االطالب.", int $status = 422, Throwable $previous = null)
    {
        parent::__construct($message, $status, $previous);
        $this->status = $status;
    }

   
    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'status' => 'error',
        ], $this->status);
    }
}
