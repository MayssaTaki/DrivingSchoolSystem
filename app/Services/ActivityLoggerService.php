<?php

namespace App\Services;
use App\Services\Interfaces\ActivityLoggerServiceInterface;

class ActivityLoggerService implements ActivityLoggerServiceInterface
{
    public function log(string $description, array $properties, string $logName, $subject = null, $causer = null, string $event)
    {
        $activity = activity($logName);

        if ($subject !== null) {
            $activity->performedOn($subject);
        }

        if ($causer !== null) {
            $activity->causedBy($causer);
        }

        $activity->withProperties($properties)
                 ->event($event)
                 ->log($description);
    }
}