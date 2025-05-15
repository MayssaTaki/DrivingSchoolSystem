<?php
namespace App\Exceptions;

use Exception;

class ScheduleExceptionConflictException extends Exception
{
    protected $message = 'يوجد بالفعل استثناء لهذا التاريخ.';
    protected $code = 409;
}