<?php

namespace App\Services;

class ActivityLoggerService
{
    public function log(string $description, array $properties, string $logName, $subject, $causer, string $event)
    {
        activity($logName)
            ->performedOn($subject)
            ->causedBy($causer)
            ->withProperties($properties)
            ->event($event)
            ->log($description);
    }
}