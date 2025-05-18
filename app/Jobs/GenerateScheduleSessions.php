<?php

namespace App\Jobs;

use App\Models\TrainingSchedule;
use App\Services\TrainingSessionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateScheduleSessions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $scheduleId) {}

    public function handle(TrainingSessionService $service): void
    {
        $schedule = TrainingSchedule::find($this->scheduleId);

        if (!$schedule) {
            \Log::warning("Schedule with ID {$this->scheduleId} not found.");
            return;
        }

        $service->generateSessionsForSchedule($schedule);
    }
}
