<?php

namespace App\Services;

use App\Repositories\PasswordResetRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Exceptions\InvalidResetTokenException;
use Illuminate\Support\Facades\Http;
use App\Services\LogService;
use App\Services\RateLimitService;
use App\Traits\LogsActivity;

class PasswordResetService
{
    use LogsActivity;

    protected PasswordResetRepository $repository;
    protected LogService $logService;
    protected RateLimitService $rateLimiter;

    public function __construct(
        PasswordResetRepository $repository,
        LogService $logService,
        RateLimitService $rateLimiter
    ) {
        $this->repository = $repository;
        $this->logService = $logService;
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

            $this->logActivity('تم إرسال رمز إعادة تعيين كلمة المرور', [
                'email' => $email
            ], 'auth', null, null, 'password_reset');

        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل إرسال رمز إعادة تعيين كلمة المرور', [
                'email' => $email,
                'error' => $e->getMessage()
            ], 'auth');

            $this->logActivity('فشل إرسال رمز إعادة تعيين كلمة المرور', [
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

        $record = $this->repository->getByEmail($data['email']);

        if (!$record || !Hash::check($data['code'], $record->token)) {
            $this->logService->log('warning', 'رمز تحقق غير صحيح', [
                'email' => $data['email'],
                'ip' => request()->ip()
            ], 'auth');

            $this->logActivity('رمز تحقق غير صحيح', [
                'email' => $data['email']
            ], 'auth', null, null, 'password_reset');

            throw new InvalidResetTokenException('رمز التحقق غير صحيح.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            $this->repository->delete($data['email']);

            $this->logService->log('warning', 'رمز إعادة تعيين منتهي الصلاحية', [
                'email' => $data['email'],
                'ip' => request()->ip()
            ], 'auth');

            $this->logActivity('رمز منتهي الصلاحية', [
                'email' => $data['email']
            ], 'auth', null, null, 'password_reset');

            throw new InvalidResetTokenException('انتهت صلاحية رمز التحقق.');
        }

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

            $this->logActivity('تمت إعادة تعيين كلمة المرور', [
                'user_id' => $user->id,
                'email' => $user->email
            ], 'auth', $user, $user, 'password_reset');

        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل إعادة تعيين كلمة المرور', [
                'email' => $data['email'],
                'error' => $e->getMessage()
            ], 'auth');

            $this->logActivity('فشل إعادة تعيين كلمة المرور', [
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
}
