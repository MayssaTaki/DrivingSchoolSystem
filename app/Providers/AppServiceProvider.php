<?php

namespace App\Providers;

use App\Models\Booking;
use App\Observers\BookingObserver;

use App\Repositories\UserRepository;
use App\Repositories\CertificateRepository;

use App\Repositories\ScheduleExceptionRepository;

use App\Repositories\FeedbackStudentRepository;

use App\Repositories\EmployeeRepository;
use App\Repositories\ExamRepository;

use App\Repositories\ActivityLogRepository;
use App\Repositories\TrainerRepository;
use App\Repositories\TrainingSessionRepository;


use App\Repositories\StudentRepository;
use App\Repositories\RefreshTokenRepository;
use App\Repositories\LogRepository;
use App\Repositories\CarRepository;
use App\Repositories\CarFaultRepository;


use App\Repositories\BookingRepository;
use App\Repositories\TrainerReviewRepository;

use App\Repositories\BookingStatusLogRepository;


use App\Repositories\TrainingSchedulesRepository;
use App\Repositories\PasswordResetRepository;
use App\Repositories\RateLimitRepository;
use App\Repositories\EmailVerificationRepository;



use App\Services\RateLimitService;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;

use App\Repositories\Contracts\FeedbackStudentRepositoryInterface;


use App\Repositories\Contracts\TrainerReviewRepositoryInterface;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Contracts\BookingStatusLogRepositoryInterface;


use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\TrainerRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\RefreshTokenRepositoryInterface;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Repositories\Contracts\CarRepositoryInterface;
use App\Repositories\Contracts\CarFaultRepositoryInterface;

use App\Repositories\Contracts\RateLimiterInterface;
use App\Repositories\Contracts\CertificateRepositoryInterface;

use App\Repositories\Contracts\PasswordResetRepositoryInterface;
use App\Repositories\Contracts\EmailVerificationRepositoryInterface;
use App\Repositories\Contracts\ScheduleExceptionRepositoryInterface;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;



use App\Repositories\Contracts\TrainingSchedulesRepositoryInterface;






use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(ActivityLogRepositoryInterface::class, ActivityLogRepository::class);
        $this->app->bind(TrainerRepositoryInterface::class, TrainerRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
        $this->app->bind(LogRepositoryInterface::class, LogRepository::class);
         $this->app->bind(\App\Services\TransactionService::class);
        $this->app->bind(CarRepositoryInterface::class, CarRepository::class);
     $this->app->bind(TrainingSchedulesRepositoryInterface::class, TrainingSchedulesRepository::class);
     $this->app->bind(RateLimiterInterface::class, RateLimitRepository::class);
$this->app->bind(
   PasswordResetRepositoryInterface::class,PasswordResetRepository::class
);
$this->app->bind(
  EmailVerificationRepositoryInterface::class,EmailVerificationRepository ::class
);
$this->app->bind(
  TrainingSessionRepositoryInterface::class, TrainingSessionRepository ::class
);
$this->app->bind(
  ScheduleExceptionRepositoryInterface::class, ScheduleExceptionRepository ::class
);
$this->app->bind(
  BookingRepositoryInterface::class, BookingRepository ::class
);
$this->app->bind(
  BookingStatusLogRepositoryInterface::class, BookingStatusLogRepository ::class
);
$this->app->bind(
 TrainerReviewRepositoryInterface::class, TrainerReviewRepository ::class
);
$this->app->bind(
FeedbackStudentRepositoryInterface::class, FeedbackStudentRepository ::class
);
$this->app->bind(
ExamRepositoryInterface::class, ExamRepository ::class
);
$this->app->bind(
CertificateRepositoryInterface::class, CertificateRepository ::class
);

        $this->app->bind(CarFaultRepositoryInterface::class, CarFaultRepository::class);


      
       
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
            Booking::observe(BookingObserver::class);

    }
}
