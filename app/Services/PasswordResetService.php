<?php

namespace App\Services;

use App\Repositories\Contracts\PasswordResetRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Exceptions\InvalidResetTokenException;
use Illuminate\Support\Facades\Http;
use App\Services\LogService;
use App\Services\Interfaces\LogServiceInterface;

use App\Services\RateLimitService;
use App\Services\Interfaces\RateLimitServiceInterface;
use App\Services\Interfaces\PasswordResetServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Traits\LogsActivity;

class PasswordResetService implements PasswordResetServiceInterface
{
    use LogsActivity;

    protected PasswordResetRepositoryInterface $repository;
    protected LogServiceInterface $logService;
    protected RateLimitServiceInterface $rateLimiter;
    protected ActivityLoggerServiceInterface $activityLogger;

    public function __construct(
        PasswordResetRepositoryInterface $repository,
        LogServiceInterface $logService,
                ActivityLoggerServiceInterface $activityLogger,

        RateLimitServiceInterface $rateLimiter
    ) {
        $this->repository = $repository;
        $this->logService = $logService;
                $this->activityLogger = $activityLogger;

        $this->rateLimiter = $rateLimiter;
    }

    public function sendResetLink(string $email): void
    {
        $context = 'password_reset_request';

        $this->rateLimiter->check($email, $context);

        $token = $this->generateResetCode();
        $this->repository->storeResetToken($email, $token);

        try {
            $response = Http::withHeaders([
                'api-key' => env('BREVO_API_KEY'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => 'Qyada School',
                    'email' => 'qyadaschool@gmail.com'
                ],
                'to' => [
                    ['email' => $email]
                ],
                'subject' => '🔐 رمز إعادة تعيين كلمة المرور',
                'htmlContent' => "<p>رمز التحقق الخاص بك هو: <strong>{$token}</strong></p>
                                 <p style='color: black; '>تحذير: لا تشارك هذا الرمز مع أي شخص.</p>"
            ]);

            if ($response->failed()) {
                throw new \Exception('فشل إرسال البريد الإلكتروني: ' . $response->body());
            }

            $this->logService->log('info', 'تم إرسال رمز إعادة تعيين كلمة المرور', [
                'email' => $email,
                'ip' => request()->ip()
            ], 'auth');

            $this->activityLogger->log('تم إرسال رمز إعادة تعيين كلمة المرور', [
                'email' => $email
            ], 'auth', null, null, 'password_reset');

        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل إرسال رمز إعادة تعيين كلمة المرور', [
                'email' => $email,
                'error' => $e->getMessage()
            ], 'auth');

            $this->activityLogger->log('فشل إرسال رمز إعادة تعيين كلمة المرور', [
                'email' => $email,
                'error' => $e->getMessage()
            ], 'auth', null, null, 'password_reset');

            throw $e;
        }
    }

  public function resetPassword(array $data): void
{
    $context = 'password_reset_attempt';

    $this->rateLimiter->check($data['email'], $context);


    try {
        $user = User::where('email', $data['email'])->firstOrFail();
        $user->update(['password' => Hash::make($data['password'])]);
        $this->repository->delete($data['email']);

        $this->rateLimiter->clear($data['email'], $context);

        $this->logService->log('info', 'تمت إعادة تعيين كلمة المرور بنجاح', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip()
        ], 'auth');

        $this->activityLogger->log('تمت إعادة تعيين كلمة المرور', [
            'user_id' => $user->id,
            'email' => $user->email
        ], 'auth', $user, $user, 'password_reset');

    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل إعادة تعيين كلمة المرور', [
            'email' => $data['email'],
            'error' => $e->getMessage()
        ], 'auth');

        $this->activityLogger->log('فشل إعادة تعيين كلمة المرور', [
            'email' => $data['email'],
            'error' => $e->getMessage()
        ], 'auth', null, null, 'password_reset');

        throw $e;
    }
}

    protected function generateResetCode(): string
    {
        return collect(range(1, 6))->map(fn () => random_int(0, 9))->implode('');
    }
    public function verifyCode(string $email, string $code): bool
{
    $context = 'password_reset_verify';

    $this->rateLimiter->check($email, $context);

    $record = $this->repository->getByEmail($email);

    if (!$record || !Hash::check($code, $record->token)) {
        $this->logService->log('warning', 'رمز تحقق غير صحيح', [
            'email' => $email,
            'ip' => request()->ip()
        ], 'auth');

      $this->activityLogger->log('رمز تحقق غير صحيح', [
            'email' => $email
        ], 'auth', null, null, 'password_reset');

        throw new InvalidResetTokenException('رمز التحقق غير صحيح.');
    }

    if (Carbon::parse($record->created_at)->addMinutes(5)->isPast()) {
        $this->repository->delete($email);

        $this->logService->log('warning', 'رمز إعادة تعيين منتهي الصلاحية', [
            'email' => $email,
            'ip' => request()->ip()
        ], 'auth');

    $this->activityLogger->log('رمز منتهي الصلاحية', [
            'email' => $email
        ], 'auth', null, null, 'password_reset');

        throw new InvalidResetTokenException('انتهت صلاحية رمز التحقق.');
    }

    $this->logService->log('info', 'تم التحقق من رمز إعادة تعيين كلمة المرور', [
        'email' => $email,
        'ip' => request()->ip()
    ], 'auth');

   $this->activityLogger->log('تم التحقق من رمز التحقق بنجاح', [
        'email' => $email
    ], 'auth', null, null, 'password_reset');

    return true;
}
public function resendCode(string $email): void
{
    $context = 'password_reset_resend';

    $this->rateLimiter->check($email, $context);

    $record = $this->repository->getByEmail($email);
    if ($record && Carbon::parse($record->created_at)->addMinutes(5)->isPast()) {
        

   
    $this->sendResetLink($email);
}

}
}