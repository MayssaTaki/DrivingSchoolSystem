<?php

namespace App\Listeners;
use App\Services\TrainingSessionService; 
use App\Events\TrainingScheduleCreated;
use App\Events\TrainingScheduleUpdated;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\TrainingSession;
class GenerateTrainingSessions
{
    /**
     * Create the event listener.
     */
    public function __construct(private TrainingSessionService $service) {}

   public function handle(object $event)
{
    $schedule = $event->schedule;

    TrainingSession::where('schedule_id', $schedule->id)->delete();

    $this->service->generateSessionsForSchedule($schedule);
}

}
