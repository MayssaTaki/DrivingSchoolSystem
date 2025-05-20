<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

use App\Models\TrainingSchedule;
use App\Jobs\GenerateScheduleSessions;
use Illuminate\Support\Facades\Cache;

class GenerateMonthlySessionsIfNeeded
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->isTrainer()) {
            $trainer = auth()->user()->trainer;

            // اجعل التنفيذ مرة واحدة يومياً لكل مدرب
            $cacheKey = 'monthly-generation-trainer-' . $trainer->id;
            if (!Cache::has($cacheKey)) {
                $schedules = TrainingSchedule::where('trainer_id', $trainer->id)
                    ->where('is_recurring', true)
                    ->whereDate('valid_to', '>=', now())
                    ->get();

                foreach ($schedules as $schedule) {
                    dispatch(new GenerateScheduleSessions($schedule->id));
                    Log::channel('scheduler')->info("⏳ Trainer {$trainer->id}: Triggered monthly session generation via middleware.");

                }

                Cache::put($cacheKey, true, now()->endOfDay());
            }
        }

        return $next($request);
    }
}
