<?php

namespace App\Jobs;
use Illuminate\Support\Facades\Log;

use App\Models\TrainingSchedule;
use App\Services\TrainingSessionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class GenerateScheduleSessions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $scheduleId) {}

    public function handle(TrainingSessionService $service): void
    {
        $schedule = TrainingSchedule::find($this->scheduleId);

        if (!$schedule) {
            Log::channel('scheduler')->warning("⛔ Schedule with ID {$this->scheduleId} not found.");
            return;
        }

        // Get last session
        $lastSession = $schedule->sessions()
            ->orderByDesc('session_date')
            ->first();

        $startFrom = $lastSession
            ? Carbon::parse($lastSession->session_date)->addDay()
            : Carbon::parse($schedule->valid_from);

        $endAt = $startFrom->copy()->addMonth();

        if ($endAt->gt(Carbon::parse($schedule->valid_to))) {
            $endAt = Carbon::parse($schedule->valid_to);
        }

        if ($startFrom->gt($endAt)) {
           Log::channel('scheduler')->info("⚠️ Schedule ID {$schedule->id}: No new period to generate. Skipped.");
            return;
        }

        // مؤقتًا نمرر التواريخ لتوليد الجلسات
        $schedule->valid_from = $startFrom->toDateString();
        $schedule->valid_to = $endAt->toDateString();

         Log::channel('scheduler')->info("✅ Generating sessions for Schedule ID {$schedule->id} from {$startFrom->toDateString()} to {$endAt->toDateString()}");

        $service->generateSessionsForSchedule($schedule);
    }
}
