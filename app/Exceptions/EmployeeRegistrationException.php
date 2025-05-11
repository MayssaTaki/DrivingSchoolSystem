<?php

namespace App\Exceptions;

use Exception;

class EmployeeRegistrationException extends Exception
{
    public function __construct($message = 'حدث خطأ أثناء تسجيل الموظف.', $code = 500)
    {
        parent::__construct($message, $code);
    }
}
