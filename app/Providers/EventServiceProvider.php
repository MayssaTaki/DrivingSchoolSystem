<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\TrainingScheduleCreated;
use App\Events\TrainingScheduleUpdated;

use App\Listeners\GenerateTrainingSessions;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TrainingScheduleCreated::class => [
            GenerateTrainingSessions::class,
        ],
          TrainingScheduleUpdated::class => [
        GenerateTrainingSessions::class,
    ],
    ];

    public function boot(): void
    {
        //
    }
}
