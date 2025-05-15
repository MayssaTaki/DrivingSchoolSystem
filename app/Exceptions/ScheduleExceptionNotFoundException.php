<?php
namespace App\Exceptions;

use Exception;

class ScheduleExceptionNotFoundException extends Exception
{
    protected $message = 'لم يتم العثور على استثناء الجدولة.';
    protected $code = 404;
}