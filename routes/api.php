<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\TrainingSchedulesController;
use App\Http\Controllers\TrainerReviewController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\BookingStatusLogController;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\FeedbackStudentController;

use App\Http\Controllers\LogController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\BookingController;

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ScheduleExceptionController;



use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Route;




Route::get('/test-email', function () {
    $response = Http::withHeaders([
        'api-key' => env('BREVO_API_KEY'),
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->post('https://api.brevo.com/v3/smtp/email', [
        'sender' => [
            'name' => 'Qyada School',
            'email' => 'qyadaschool@gmail.com' // بريد مُفعّل في Brevo
        ],
        'to' => [
            ['email' => 'maessataki@gmail.com']
        ],
        'subject' => '📨 حظا سعيد في الحياة معلم حمزة',
        'htmlContent' => '<p>✅</p>',
    ]);

    return $response->json(); // يعرض نتيجة الإرسال
});














Route::get('/test-middleware', function () {
    return '✅ Middleware ran!';
})->middleware([ App\Http\Middleware\GenerateMonthlySessionsIfNeeded::class]);



Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('/refresh', [AuthController::class, 'refreshToken']);
Route::post('/send-reset-code', [PasswordResetController::class, 'sendResetCode']);
Route::post('/verify-reset-code', [PasswordResetController::class, 'verifyResetCode']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/resend-reset-code', [PasswordResetController::class, 'resendResetCode']);

Route::post('/resend-email-code', [EmailVerificationController::class, 'resend']);
Route::post('/email/verify', [EmailVerificationController::class, 'verify']);


Route::middleware('auth:api','adminOnly')->group(function () {
    Route::get('/admin/logs', [LogController::class, 'index']);
    Route::get('/activity-log', [ActivityLogController::class, 'getAllActivityLogs']);
    Route::post('/employee/register', [EmployeeController::class, 'register']);
Route::get('/employees', [EmployeeController::class, 'getAllEmployes']);
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
Route::get('/employees/count', [EmployeeController::class, 'countEmployees']);
});







Route::prefix('exams')->group(function () {
    Route::post('/{id}/submit', [ExamController::class, 'submit']);
});
Route::post('/exams', [ExamController::class, 'store'])->middleware('auth:api');
 Route::get('/exams/{id}', [ExamController::class, 'show'])->middleware('auth:api');
    Route::get('/exam', [ExamController::class, 'index'])->middleware('auth:api');
Route::post('/exams/start', [ExamController::class, 'start'])->middleware('auth:api');
Route::post('/exams/submit', [ExamController::class, 'submitAnswers'])->middleware('auth:api');
Route::post('/start-mixed-exam', [ExamController::class, 'startMixedExam']);








Route::post('/trainer/register', [TrainerController::class, 'register']);
Route::get('/trainers', [TrainerController::class, 'getAllTrainers'])->middleware('auth:api');
Route::delete('/trainers/{id}', [TrainerController::class, 'destroy'])->middleware('auth:api');
Route::put('/trainers/{trainer}', [TrainerController::class, 'update'])->middleware('auth:api');
Route::get('/trainers/count', [TrainerController::class, 'countTrainers'])->middleware('auth:api');
Route::get('/trainersApprove', [TrainerController::class, 'getAllTrainersApprove'])->middleware('auth:api');
Route::post('/trainers/{id}/approve', [TrainerController::class, 'approve'])->middleware('auth:api');
Route::post('/trainers/{id}/reject', [TrainerController::class, 'reject'])->middleware('auth:api');
Route::get('/count/approved', [TrainerController::class, 'approved'])->middleware('auth:api');
Route::get('/count/rejected', [TrainerController::class, 'rejected'])->middleware('auth:api');
Route::get('/count/pened', [TrainerController::class, 'pened'])->middleware('auth:api');
Route::get('/trainer/{id}/schedules', [TrainingSchedulesController::class, 'showByTrainer'])->middleware('auth:api');
Route::post('/training-schedules', [TrainingSchedulesController::class, 'store'])->middleware('auth:api');
Route::put('/training-schedules/{id}/activate', [TrainingSchedulesController::class, 'activate'])->middleware('auth:api');
Route::put('/training-schedules/{id}/deactivate', [TrainingSchedulesController::class, 'deactivate'])->middleware('auth:api');

Route::get('/trainer-exceptions', [ScheduleExceptionController::class, 'getTrainerExceptions'])->middleware('auth:api');
Route::post('/schedule-exceptions', [ScheduleExceptionController::class, 'store'])->middleware('auth:api');
Route::put('/schedule-exceptions/{exception}', [ScheduleExceptionController::class, 'update']);
Route::delete('/schedule-exceptions/{exception}', [ScheduleExceptionController::class, 'destroy']);
Route::post('/schedule-exceptions/{id}/approve', [ScheduleExceptionController::class, 'approve'])->middleware('auth:api');
Route::post('/schedule-exceptions/{id}/reject', [ScheduleExceptionController::class, 'reject'])->middleware('auth:api');

Route::get('/trainer-sessions/counts', [TrainingSessionController::class, 'getSessionCounts'])->middleware('auth:api');
Route::get('/recommended-sessions', [TrainingSessionController::class, 'getRecommendedSessions'])->middleware('auth:api');


Route::post('/bookings', [BookingController::class, 'store'])->middleware('auth:api');
Route::post('/bookings/{id}/complete', [BookingController::class, 'complete'])->middleware('auth:api');
Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancell'])->middleware('auth:api');
Route::post('/auto-book-session', [BookingController::class, 'autoBook'])->middleware('auth:api');
Route::post('/booking/{id}/start', [BookingController::class, 'startSession']);

    Route::get('/bookings/{bookingId}/status-logs', [BookingStatusLogController::class, 'index']);


Route::get('/trainer/bookings', [BookingController::class, 'getTrainerBookedSessions']);


Route::get('/trainer-sessions', [TrainingSessionController::class, 'getTrainerSessions'])->middleware('auth:api');

Route::get('/trainer/{trainerId}/reviews', [TrainerReviewController::class, 'index']);

    Route::post('trainer-reviews', [TrainerReviewController::class, 'store'])->middleware('auth:api');
  Route::get('trainer-reviews/pending', [TrainerReviewController::class, 'pending']);
        Route::post('trainer-reviews/{id}/approve', [TrainerReviewController::class, 'approve'])->middleware('auth:api');
        Route::post('trainer-reviews/{id}/reject', [TrainerReviewController::class, 'reject'])->middleware('auth:api');
    Route::post('/feedback/student', [FeedbackStudentController::class, 'store']);



Route::post('/student/register', [StudentController::class, 'register']);
Route::get('/students', [StudentController::class, 'getAllStudents'])->middleware('auth:api');
Route::delete('/students/{id}', [StudentController::class, 'destroy'])->middleware('auth:api');
Route::put('/students/{student}', [StudentController::class, 'update'])->middleware('auth:api');
Route::get('/students/count', [StudentController::class, 'countStudents'])->middleware('auth:api');



Route::get('/cars', [CarController::class, 'getAllCars'])->middleware('auth:api');
Route::get('/cars/count', [CarController::class, 'countCars'])->middleware('auth:api');
Route::post('/car/add', [CarController::class, 'add'])->middleware('auth:api');
Route::delete('/cars/{id}', [CarController::class, 'destroy'])->middleware('auth:api');
Route::put('/cars/{car}', [CarController::class, 'update'])->middleware('auth:api');




