<?php
namespace App\Providers;

use App\Models\Employee;
use App\Models\LogEntry;

use App\Policies\LogEntryPolicy;

use App\Policies\EmployeePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Booking;
use App\Policies\BookingPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
            \App\Models\Booking::class => \App\Policies\BookingPolicy::class,

        \App\Models\Trainer::class => \App\Policies\TrainerPolicy::class,
        \App\Models\Student::class => \App\Policies\StudentPolicy::class,
    \App\Models\ScheduleException::class => \App\Policies\ScheduleExceptionPolicy::class,
    \App\Models\TrainingSchedule::class => \App\Policies\TrainingSchedulePolicy::class,
    \App\Models\TrainingReview::class => \App\Policies\TrainingReviewPolicy::class,
        \App\Models\Exam::class => \App\Policies\ExamPolicy::class,
        \App\Models\License::class => \App\Policies\LicensePolicy::class,
        \App\Models\LicenseRequest::class => \App\Policies\LicenseRequestPolicy::class,
                \App\Models\Post::class => \App\Policies\PostPolicy::class,



    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

       
    }
}
