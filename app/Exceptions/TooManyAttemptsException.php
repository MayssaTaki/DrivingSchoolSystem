<?php
namespace App\Exceptions;

use Exception;

class TooManyAttemptsException extends Exception
{
    protected $seconds;

    public function __construct($seconds)
    {
        parent::__construct("عدة محاولات كثيرة. اعد المحاولة بعد  {$seconds} ثانية.");
        $this->seconds = $seconds;
    }

    public function getSeconds()
    {
        return $this->seconds;
    }
}