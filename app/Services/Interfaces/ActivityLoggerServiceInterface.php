<?php
namespace App\Services\Interfaces;

interface ActivityLoggerServiceInterface
{


        public function log(string $description, array $properties, string $logName, $subject, $causer, string $event);
        
}