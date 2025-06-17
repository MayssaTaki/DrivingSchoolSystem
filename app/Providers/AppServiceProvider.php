<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Models & Observers
use App\Models\Booking;
use App\Observers\BookingObserver;

// Repositories
use App\Repositories\UserRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\ActivityLogRepository;
use App\Repositories\TrainerRepository;
use App\Repositories\StudentRepository;
use App\Repositories\RefreshTokenRepository;
use App\Repositories\LogRepository;
use App\Repositories\CarRepository;
use App\Repositories\CarFaultRepository;
use App\Repositories\TrainingSchedulesRepository;
use App\Repositories\RateLimitRepository;
use App\Repositories\PasswordResetRepository;
use App\Repositories\EmailVerificationRepository;
use App\Repositories\ScheduleExceptionRepository;
use App\Repositories\TrainingSessionRepository;
use App\Repositories\BookingRepository;
use App\Repositories\TrainerReviewRepository;
use App\Repositories\FeedbackStudentRepository;
use App\Repositories\ExamRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\BookingStatusLogRepository;

// Repository Interfaces
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\TrainerRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\RefreshTokenRepositoryInterface;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Repositories\Contracts\CarRepositoryInterface;
use App\Repositories\Contracts\CarFaultRepositoryInterface;
use App\Repositories\Contracts\TrainingSchedulesRepositoryInterface;
use App\Repositories\Contracts\RateLimiterInterface;
use App\Repositories\Contracts\PasswordResetRepositoryInterface;
use App\Repositories\Contracts\EmailVerificationRepositoryInterface;
use App\Repositories\Contracts\ScheduleExceptionRepositoryInterface;
use App\Repositories\Contracts\TrainingSessionRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\TrainerReviewRepositoryInterface;
use App\Repositories\Contracts\FeedbackStudentRepositoryInterface;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Repositories\Contracts\BookingStatusLogRepositoryInterface;

// Services & Interfaces
use App\Services\ExamService;
use App\Services\TrainingSessionService;
use App\Services\TrainerReviewService;
use App\Services\RateLimitService;
use App\Services\PasswordResetService;
use App\Services\ScheduleExceptionService;
use App\Services\LogService;
use App\Services\FeedbackStudentService;
use App\Services\CarFaultService;
use App\Services\TransactionService;
use App\Services\ActivityLoggerService;
use App\Services\ActivityLogService;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\TrainingSchedulesService;
use App\Services\TrainerService;
use App\Services\StudentService;
use App\Services\EmailVerificationService;
use App\Services\CertificateService;
use App\Services\CarService;
use App\Services\BookingStatusLogService;
use App\Services\BookingService;
use App\Services\EmployeeService;


use App\Services\Interfaces\ExamServiceInterface;
use App\Services\Interfaces\TrainingSessionServiceInterface;
use App\Services\Interfaces\TrainerReviewServiceInterface;
use App\Services\Interfaces\ScheduleExceptionServiceInterface;
use App\Services\Interfaces\PasswordResetServiceInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\FeedbackStudentServiceInterface;
use App\Services\Interfaces\CarFaultServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\ActivityLogServiceInterface;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\RateLimitServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use App\Services\Interfaces\TrainingSchedulesServiceInterface;
use App\Services\Interfaces\TrainerServiceInterface;
use App\Services\Interfaces\StudentServiceInterface;
use App\Services\Interfaces\EmailVerificationServiceInterface;
use App\Services\Interfaces\CertificateServiceInterface;
use App\Services\Interfaces\CarServiceInterface;
use App\Services\Interfaces\BookingStatusLogServiceInterface;
use App\Services\Interfaces\BookingServiceInterface;
use App\Services\Interfaces\EmployeeServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Repositories bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(ActivityLogRepositoryInterface::class, ActivityLogRepository::class);
        $this->app->bind(TrainerRepositoryInterface::class, TrainerRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
        $this->app->bind(LogRepositoryInterface::class, LogRepository::class);
        $this->app->bind(CarRepositoryInterface::class, CarRepository::class);
        $this->app->bind(CarFaultRepositoryInterface::class, CarFaultRepository::class);
        $this->app->bind(TrainingSchedulesRepositoryInterface::class, TrainingSchedulesRepository::class);
        $this->app->bind(RateLimiterInterface::class, RateLimitRepository::class);
        $this->app->bind(PasswordResetRepositoryInterface::class, PasswordResetRepository::class);
        $this->app->bind(EmailVerificationRepositoryInterface::class, EmailVerificationRepository::class);
        $this->app->bind(TrainingSessionRepositoryInterface::class, TrainingSessionRepository::class);
        $this->app->bind(ScheduleExceptionRepositoryInterface::class, ScheduleExceptionRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);
        $this->app->bind(BookingStatusLogRepositoryInterface::class, BookingStatusLogRepository::class);
        $this->app->bind(TrainerReviewRepositoryInterface::class, TrainerReviewRepository::class);
        $this->app->bind(FeedbackStudentRepositoryInterface::class, FeedbackStudentRepository::class);
        $this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
        $this->app->bind(CertificateRepositoryInterface::class, CertificateRepository::class);

        

        $this->app->bind(LogServiceInterface::class, LogService::class);
        $this->app->bind(ActivityLoggerServiceInterface::class, ActivityLoggerService::class);
        $this->app->bind(ActivityLogServiceInterface::class, ActivityLogService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(RateLimitServiceInterface::class, RateLimitService::class);
        $this->app->bind(TransactionServiceInterface::class, TransactionService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TrainingSessionServiceInterface::class, TrainingSessionService::class);
        $this->app->bind(TrainingSchedulesServiceInterface::class, TrainingSchedulesService::class);
        $this->app->bind(TrainerServiceInterface::class, TrainerService::class);
        $this->app->bind(TrainerReviewServiceInterface::class, TrainerReviewService::class);
        $this->app->bind(StudentServiceInterface::class, StudentService::class);
        $this->app->bind(ScheduleExceptionServiceInterface::class, ScheduleExceptionService::class);
        $this->app->bind(PasswordResetServiceInterface::class, PasswordResetService::class);
        $this->app->bind(FeedbackStudentServiceInterface::class, FeedbackStudentService::class);
        $this->app->bind(ExamServiceInterface::class, ExamService::class);
        $this->app->bind(EmployeeServiceInterface::class,EmployeeService::class);
        $this->app->bind(EmailVerificationServiceInterface::class, EmailVerificationService::class);
        $this->app->bind(CertificateServiceInterface::class, CertificateService::class);
        $this->app->bind(CarServiceInterface::class, \App\Services\CarService::class);
        $this->app->bind(CarFaultServiceInterface::class, CarFaultService::class);
        $this->app->bind(BookingStatusLogServiceInterface::class, BookingStatusLogService::class);
        $this->app->bind(BookingServiceInterface::class, BookingService::class);
   


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Booking::observe(BookingObserver::class);
    }
}
