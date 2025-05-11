<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\TrainingSchedulesController;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\CarController;

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
            ['email' => 'hamzaasaad80@gmail.com']
        ],
        'subject' => '📨 حظا سعيد في الحياة معلم حمزة',
        'htmlContent' => '<p>✅</p>',
    ]);

    return $response->json(); // يعرض نتيجة الإرسال
});


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('/refresh', [AuthController::class, 'refreshToken'])->middleware('auth:api');


Route::middleware('adminOnly')->group(function () {
    Route::get('/admin/logs', [LogController::class, 'index']);
    Route::get('/activity-log', [ActivityLogController::class, 'getAllActivityLogs']);
    Route::post('/employee/register', [EmployeeController::class, 'register']);
Route::get('/employees', [EmployeeController::class, 'getAllEmployes']);
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
Route::get('/employees/count', [EmployeeController::class, 'countEmployees']);
});







Route::post('/trainer/register', [TrainerController::class, 'register']);
Route::get('/trainers', [TrainerController::class, 'getAllTrainers']);
Route::delete('/trainers/{id}', [TrainerController::class, 'destroy']);
Route::put('/trainers/{trainer}', [TrainerController::class, 'update']);
Route::get('/trainers/count', [TrainerController::class, 'countTrainers']);
Route::get('/trainersApprove', [TrainerController::class, 'getAllTrainersApprove']);
Route::post('/trainers/{id}/approve', [TrainerController::class, 'approve']);
Route::post('/trainers/{id}/reject', [TrainerController::class, 'reject']);
Route::get('/count/approved', [TrainerController::class, 'approved']);
Route::get('/count/rejected', [TrainerController::class, 'rejected']);
Route::get('/count/pened', [TrainerController::class, 'pened']);
Route::get('/trainer/{id}/schedules', [TrainingSchedulesController::class, 'showByTrainer']);





Route::post('/student/register', [StudentController::class, 'register']);
Route::get('/students', [StudentController::class, 'getAllStudents']);
Route::delete('/students/{id}', [StudentController::class, 'destroy']);
Route::put('/students/{student}', [StudentController::class, 'update']);
Route::get('/students/count', [StudentController::class, 'countStudents']);



Route::get('/cars', [CarController::class, 'getAllCars']);
Route::get('/cars/count', [CarController::class, 'countCars']);
Route::post('/car/add', [CarController::class, 'add']);
Route::delete('/cars/{id}', [CarController::class, 'destroy']);
Route::put('/cars/{car}', [CarController::class, 'update']);




