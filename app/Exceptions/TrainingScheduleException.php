<?php
namespace App\Exceptions;

use Exception;

class TrainingScheduleException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => (  $this->getMessage())
        ], $this->getCode() ?: 400);
    }
}
