<?php

namespace App\Exceptions;

use Exception;

class CarUnavailableException extends Exception
{
    public function __construct($message = "السيارة غير متاحة في هذا الوقت", $code = 409)
    {
        parent::__construct($message, $code);
    }
}
