<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\TrainingSchedulesController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PracticalExamController;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\FeedbackStudentController;

use App\Http\Controllers\LogController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarFaultController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\LicenseRequestController;

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExamController;

use App\Http\Controllers\BookingStatusLogController;
use App\Http\Controllers\TrainerReviewController;



use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ScheduleExceptionController;
use App\Http\Controllers\CertificateController;




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


Route::post('exam-schedules', [PracticalExamController::class, 'store'])
     ->middleware(['auth:api']);
Route::get('practical-exams', [PracticalExamController::class, 'index'])
     ->middleware(['auth:api']);
Route::get('practical-exams/my', [PracticalExamController::class, 'mySchedules'])
     ->middleware(['auth:api']);
Route::put('{id}/mark-passed', [PracticalExamController::class, 'markAsPassed'])->middleware(['auth:api']);
Route::put('{id}/mark-failed', [PracticalExamController::class, 'markAsFailed'])->middleware(['auth:api']);
Route::put('{id}/mark-absent', [PracticalExamController::class, 'markAsAbsent'])->middleware(['auth:api']);
Route::get('stats/count', [PracticalExamController::class, 'countByStatus'])->middleware(['auth:api']);
Route::get('stats/failed-absent', [PracticalExamController::class, 'failedOrAbsentStudents'])->middleware(['auth:api']);
Route::get('stats/success-ratio', [PracticalExamController::class, 'successRatio'])->middleware(['auth:api']);





Route::get('/posts', [PostController::class,'index'])->middleware('auth:api');
Route::post('/posts/create', [PostController::class, 'store'])->middleware('auth:api');
Route::put('/posts/{id}', [PostController::class, 'update'])->middleware('auth:api');
Route::delete('/posts/delete/{id}', [PostController::class, 'destroy'])->middleware('auth:api');
Route::post('/posts/{postId}/like', [LikeController::class, 'toggle'])->middleware('auth:api');
Route::get('/posts/{post}/liked-students', [LikeController::class, 'studentsByPost']) ->middleware('auth:api');
Route::get('/posts/count', [PostController::class, 'countPosts'])->middleware('auth:api');


Route::get('/licenses', [LicenseController::class, 'index'])->middleware('auth:api');
Route::post('/licenses/create', [LicenseController::class, 'store'])->middleware('auth:api');
Route::put('/licenses/{license}', [LicenseController::class, 'update'])->middleware('auth:api');
Route::get('/count', [LicenseController::class, 'countLicenses'])->middleware('auth:api');

Route::post('/license-request', [LicenseRequestController::class, 'store'])->middleware('auth:api');
Route::get('/license-requests', [LicenseRequestController::class, 'index'])->middleware('auth:api');
Route::get('/license-requests/my', [LicenseRequestController::class, 'myRequests'])->middleware('auth:api');
Route::post('/license-requests/{id}/approve', [LicenseRequestController::class, 'approve'])->middleware('auth:api');
Route::post('/license-requests/{id}/reject', [LicenseRequestController::class, 'reject'])->middleware('auth:api');
Route::get('/license-requests/pending', [LicenseRequestController::class, 'getPending'])->middleware('auth:api');
Route::get('/license-requests/approved', [LicenseRequestController::class, 'getApproved'])->middleware('auth:api');
Route::get('/license-requests/rejected', [LicenseRequestController::class, 'getRejected'])->middleware('auth:api');
Route::get('/count/approve', [LicenseRequestController::class, 'countApproved'])->middleware('auth:api');
Route::get('/count/pending', [LicenseRequestController::class, 'countPending'])->middleware('auth:api');
Route::get('/count/reject', [LicenseRequestController::class, 'countRejected'])->middleware('auth:api');
Route::get('/monthly',[LicenseRequestController::class, 'monthly'])->middleware('auth:api');
Route::get('/type',[LicenseRequestController::class, 'typeStats'])->middleware('auth:api');
Route::get('/mostRequest',[LicenseRequestController::class, 'mostRequestedLicenses'])->middleware('auth:api');




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
Route::get('/exam/{trainerId}/type/{type}', [ExamController::class, 'showByTrainerAndType']);
    Route::get('/exam/{trainerId}', [ExamController::class, 'indexByTrainer']);
});




Route::prefix('exams')->group(function () {
    Route::post('/{id}/submit', [ExamController::class, 'submit']);
});
Route::post('/exams', [ExamController::class, 'store'])->middleware('auth:api');
 Route::get('/exam/type/{type}', [ExamController::class, 'showByType'])->middleware('auth:api');
    Route::get('/exam', [ExamController::class, 'index'])->middleware('auth:api');
Route::post('/exams/start', [ExamController::class, 'start'])->middleware('auth:api');
Route::post('/exams/submit', [ExamController::class, 'submitAnswers'])->middleware('auth:api');
Route::post('/generate', [ExamController::class, 'showRandomQuestions'])->middleware('auth:api');


Route::get('/student/evaluation', [ExamController::class, 'evaluate'])->middleware('auth:api');
Route::get('/certificate/generate/{studentId}', [CertificateController::class, 'generate']);
Route::get('/certificate/download', [CertificateController::class, 'download']);




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

Route::get('/trainer-exception', [ScheduleExceptionController::class, 'getAllTrainersExceptions'])->middleware('auth:api');
Route::get('/trainer-exceptions', [ScheduleExceptionController::class, 'getTrainerExceptions'])->middleware('auth:api');
Route::post('/schedule-exceptions', [ScheduleExceptionController::class, 'store'])->middleware('auth:api');
Route::put('/schedule-exceptions/{exception}', [ScheduleExceptionController::class, 'update']);
Route::delete('/schedule-exceptions/{exception}', [ScheduleExceptionController::class, 'destroy']);
Route::post('/schedule-exceptions/{id}/approve', [ScheduleExceptionController::class, 'approve'])->middleware('auth:api');
Route::post('/schedule-exceptions/{id}/reject', [ScheduleExceptionController::class, 'reject'])->middleware('auth:api');
   Route::get('/pending', [ScheduleExceptionController::class, 'getPending'])->middleware('auth:api');
    Route::get('/approved', [ScheduleExceptionController::class, 'getApproved'])->middleware('auth:api');
    Route::get('/rejected', [ScheduleExceptionController::class, 'getRejected'])->middleware('auth:api');

Route::get('/trainer-sessions/counts', [TrainingSessionController::class, 'getSessionCounts'])->middleware('auth:api');
Route::get('/recommended-sessions', [TrainingSessionController::class, 'getRecommendedSessions'])->middleware('auth:api');


Route::post('/bookings', [BookingController::class, 'store'])->middleware('auth:api');
Route::post('/bookings/{id}/complete', [BookingController::class, 'complete'])->middleware('auth:api');
Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancell'])->middleware('auth:api');
Route::post('/auto-book-session', [BookingController::class, 'autoBook'])->middleware('auth:api');
Route::post('/booking/{id}/start', [BookingController::class, 'startSession']);

    Route::get('/bookings/status-logs', [BookingStatusLogController::class, 'index'])->middleware('auth:api');
Route::get('/bookings/status-logs/export', [BookingStatusLogController::class, 'export'])->middleware('auth:api');
Route::get('/booking-status-logs/export-pdf', [BookingStatusLogController::class, 'exportPdf']);


Route::get('/trainer/bookings', [BookingController::class, 'getTrainerBookedSessions'])->middleware('auth:api');
Route::get('/student/bookings', [BookingController::class, 'getStudentBookedSessions'])->middleware('auth:api');


Route::get('/trainer-sessions', [TrainingSessionController::class, 'getTrainerSessions'])->middleware('auth:api');
Route::get('/trainer-sessions/schedule', [TrainingSessionController::class, 'getScheduleSessions'])->middleware('auth:api');

Route::get('/trainer/{trainerId}/reviews', [TrainerReviewController::class, 'index']);
 Route::get('/pending/reviews', [TrainerReviewController::class, 'getPending'])->middleware('auth:api');
    Route::get('/approved/reviews', [TrainerReviewController::class, 'getApproved'])->middleware('auth:api');
    Route::get('/rejected/reviews', [TrainerReviewController::class, 'getRejected'])->middleware('auth:api');
    Route::post('trainer-reviews', [TrainerReviewController::class, 'store'])->middleware('auth:api');
    Route::get('/trainers/stats', [TrainerReviewController::class, 'topAndWorst'])->middleware('auth:api');

  Route::get('trainer-reviews/pending', [TrainerReviewController::class, 'pending']);
        Route::post('trainer-reviews/{id}/approve', [TrainerReviewController::class, 'approve'])->middleware('auth:api');
        Route::post('trainer-reviews/{id}/reject', [TrainerReviewController::class, 'reject'])->middleware('auth:api');
        
    Route::post('/feedback/student', [FeedbackStudentController::class, 'store'])->middleware('auth:api');
Route::get('/feedback-students', [FeedbackStudentController::class, 'index'])->middleware('auth:api');
Route::get('/trainer/feedbacks', [FeedbackStudentController::class, 'getTrainerFeedbacks'])->middleware('auth:api');
Route::get('/feedbacks/all', [FeedbackStudentController::class, 'getAllFeedbacks'])->middleware('auth:api');



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

Route::post('/add', [CarFaultController::class, 'store'])->middleware('auth:api');
Route::get('/car-faults', [CarFaultController::class, 'index'])->middleware('auth:api');
Route::get('/trainer/car-faults', [CarFaultController::class, 'getTrainerFaults'])->middleware('auth:api');
Route::post('car-faults/send-to-repair', [CarFaultController::class, 'sendToRepair'])->middleware('auth:api');
Route::post('car-faults/resolve', [CarFaultController::class, 'sendToResolved'])->middleware('auth:api');

 Route::get('/faults-per-car', [CarFaultController::class, 'countFaultsPerCar'])->middleware('auth:api');
    Route::get('/top-faulted-cars', [CarFaultController::class, 'getTopFaultedCars'])->middleware('auth:api');
    Route::get('/monthly-faults', [CarFaultController::class, 'getMonthlyFaultsCount'])->middleware('auth:api');
    Route::get('/average-monthly-faults', [CarFaultController::class, 'getAverageMonthlyFaultsPerCar'])->middleware('auth:api');
    Route::get('/status-count-per-car', [CarFaultController::class, 'getFaultsStatusCountPerCar'])->middleware('auth:api');


