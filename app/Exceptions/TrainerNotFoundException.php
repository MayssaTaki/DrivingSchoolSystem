<?php
namespace App\Exceptions;

use Exception;

class TrainerNotFoundException extends Exception {
    public function __construct($message = 'المدرب غير موجود', $code = 500)
    {
        parent::__construct($message, $code);
    }
}