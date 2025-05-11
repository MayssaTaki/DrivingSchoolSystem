<?php

namespace App\Exceptions;

use Exception;

class TrainerRegistrationException extends Exception
{
    public function __construct($message = 'حدث خطأ أثناء تسجيل المدرب.', $code = 500)
    {
        parent::__construct($message, $code);
    }
}
