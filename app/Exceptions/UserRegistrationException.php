<?php

namespace App\Exceptions;

use Exception;

class UserRegistrationException extends Exception
{
    public function __construct($message = 'حدث خطأ أثناء تسجيل المستخدم.', $code = 500)
    {
        parent::__construct($message, $code);
    }
}
