<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrainingSchedule;
use App\Jobs\GenerateScheduleSessions;
use Carbon\Carbon;

class DispatchMonthlySessionJobs extends Command
{
    protected $signature = 'training:dispatch-monthly-jobs';
    protected $description = 'Dispatch jobs to generate training sessions for the upcoming month';

    public function handle()
    {
        $today = Carbon::today();
        $nextMonthStart = $today->copy()->startOfMonth()->addMonth();
        $nextMonthEnd = $today->copy()->endOfMonth()->addMonth();

        $schedules = TrainingSchedule::where('is_recurring', true)
            ->whereDate('valid_to', '>=', $nextMonthStart)
            ->get();

        foreach ($schedules as $schedule) {
            GenerateScheduleSessions::dispatch($schedule->id);
        }

        $this->info('Monthly session generation jobs dispatched successfully.');
    }
}
