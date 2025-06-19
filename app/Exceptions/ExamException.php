<?php
namespace App\Exceptions;

use Exception;

class ExamException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => (  $this->getMessage())
        ], $this->getCode() ?: 400);
    }
}
