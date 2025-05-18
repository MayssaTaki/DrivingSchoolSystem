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
        $monthFromNow = $today->copy()->addMonth();

        $schedules = TrainingSchedule::where('is_recurring', true)
            ->whereDate('valid_from', '<=', $monthFromNow)
            ->whereDate('valid_to', '>=', $today)
            ->get();

        foreach ($schedules as $schedule) {
GenerateScheduleSessions::dispatch($schedule->id);
        }

        $this->info('Jobs dispatched successfully.');
    }
}
