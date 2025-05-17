<?php

namespace App\Listeners;
use App\Services\TrainingSessionService; 
use App\Events\TrainingScheduleCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateTrainingSessions
{
    /**
     * Create the event listener.
     */
    public function __construct(private TrainingSessionService $service) {}

    public function handle(TrainingScheduleCreated $event)
    {
        $this->service->generateSessionsForSchedule($event->schedule);
    }
}
