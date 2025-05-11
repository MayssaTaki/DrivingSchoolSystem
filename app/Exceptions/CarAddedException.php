<?php

namespace App\Exceptions;

use Exception;

class CarAddedException extends Exception
{
    public function __construct($message = 'حدث خطأ أثناء اضافة سيارة .', $code = 500)
    {
        parent::__construct($message, $code);
    }
}
