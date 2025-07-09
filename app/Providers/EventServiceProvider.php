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
     \App\Events\ScheduleNeedsSessionGeneration::class => [
        \App\Listeners\DispatchGenerateJobForSchedule::class,
    ],
    \App\Events\TrainerRegistered::class => [
        \App\Listeners\SendTrainerRegisteredNotification::class,
    ],
    \App\Events\StudentRegistered::class => [
        \App\Listeners\SendStudentRegisteredNotification::class,
    ],
     \App\Events\TrainerApproved::class => [
        \App\Listeners\SendTrainerApprovedNotification::class,
     ],
       \App\Events\TrainerRejected::class => [
        \App\Listeners\SendTrainerRejectedNotification::class,
     ],
       \App\Events\CarAdded::class => [
        \App\Listeners\SendCarAddedNotification::class,
    ],
    \App\Events\CarFaultSubmitted::class => [
        \App\Listeners\SendCarFaultSubmittedNotification::class,
    ],
      \App\Events\CarMarkedAsInRepair::class => [
        \App\Listeners\SendCarMarkedAsInRepairNotification::class,
    ],
      \App\Events\CarMarkedAsResolved::class => [
        \App\Listeners\SendCarMarkedAsResolvedNotification::class,
    ],
     \App\Events\TrainingScheduleActivated::class => [
        \App\Listeners\SendTrainingScheduleActivatedNotification::class,
    ],
      \App\Events\TrainingScheduleDeactivated::class => [
        \App\Listeners\SendTrainingScheduleDeactivatedNotification::class,
    ],
        \App\Events\TrainerExceptionCreated::class => [
        \App\Listeners\SendTrainerExceptionCreatedNotification::class,
    ],
      \App\Events\ExceptionApproved::class => [
        \App\Listeners\SendExceptionApprovedNotification::class,
    ],
       \App\Events\ExceptionRejected::class => [
        \App\Listeners\SendExceptionRejectedNotification::class,
    ],
        \App\Events\TrainerReviewed::class => [
        \App\Listeners\SendTrainerReviewedNotification::class,
    ],
     \App\Events\ReviewApproved::class => [
        \App\Listeners\SendReviewApprovedNotification::class,
    ],
       \App\Events\ReviewRejected::class => [
        \App\Listeners\SendReviewRejectedNotification::class,
    ],
     \App\Events\FeedbackGiven::class => [
        \App\Listeners\SendFeedbackGivenNotification::class,
    ],
      \App\Events\LicenseCreated::class => [
        \App\Listeners\SendLicenseCreatedNotification::class,
    ],
    ];

    public function boot(): void
    {
        //
    }
}
