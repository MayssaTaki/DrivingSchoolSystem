<?php

namespace App\Exceptions;

use Exception;

class StudentRegistrationException extends Exception
{
    public function __construct($message = 'حدث خطأ أثناء تسجيل الطالب.', $code = 500)
    {
        parent::__construct($message, $code);
    }
}
