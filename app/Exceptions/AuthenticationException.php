<?php
namespace App\Exceptions;

use Exception;

class AuthenticationException extends Exception
{
    public function __construct($message = 'بيانات تسجيل الدخول غير صحيحة.', $code = 401)
    {
        parent::__construct($message, $code);
    }
}
