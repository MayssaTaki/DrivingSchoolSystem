<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
  
    protected function logActivity(
        string $message,
        array $properties = [],
        string $logName = 'default',
        Model $subject = null,
        Model $causer = null,
        string $event = null
    ): void {
        $activity = activity($logName)->withProperties($properties);

        if ($event === 'login' || $event === 'logout') {
            $activity->withProperties(array_merge([
                'ip' => request()->ip(),
                'agent' => request()->userAgent(),
            ], $properties));
        }

        if ($causer) {
            $activity->causedBy($causer);
        } elseif (Auth::check()) {
            $activity->causedBy(Auth::user());
        }

        if ($subject) {
            $activity->performedOn($subject);
        }

        if ($event) {
            $activity->event($event);
        }

        $activity->log($message);
    }
}
