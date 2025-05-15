<?php

namespace App\Services;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Repositories\Contracts\EmailVerificationRepositoryInterface;
use App\Services\RateLimitService;
use App\Services\LogService;
use Request;
use App\Repositories\Contracts\UserRepositoryInterface;

class EmailVerificationService
{
    use LogsActivity;

    protected EmailVerificationRepositoryInterface $repository;
    protected RateLimitService $rateLimiter;
    protected LogService $logService;
        protected UserRepositoryInterface $userRepository;


    public function __construct(
        EmailVerificationRepositoryInterface $repository,
        RateLimitService $rateLimiter,
        LogService $logService,
                UserRepositoryInterface $userRepository

    ) {
        $this->repository = $repository;
        $this->rateLimiter = $rateLimiter;
        $this->logService = $logService;
         $this->userRepository = $userRepository;
    }

    protected function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function sendVerificationCode(User $user): void
    {
        $context = 'email_verification';

        $this->rateLimiter->check($user->email, $context);

        $code = $this->generateCode();
       $expiresAt = now()->addMinutes(5); 
$this->repository->create($user->email, $code, $expiresAt);

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
                    ['email' => $user->email]
                ],
                'subject' => '🔐رمز تفعيل البريد الإلكتروني ',
                'htmlContent' => "<p>رمز التحقق الخاص بك هو: <strong>{$code}</strong></p>
                                 <p style='color: black;'>يرجى عدم مشاركة هذا الرمز مع أي أحد.</p>"
            ]);

            if ($response->failed()) {
                throw new \Exception('فشل إرسال بريد التحقق: ' . $response->body());
            }

            $this->logService->log('info', 'تم إرسال رمز تفعيل البريد الإلكتروني', [
                'email' => $user->email,
                'ip' => request()->ip(),
            ], 'auth');

            $this->logActivity('تم إرسال رمز التحقق', [
                'email' => $user->email
            ], 'auth', $user, $user, 'email_verification');

        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل إرسال رمز تفعيل البريد الإلكتروني', [
                'email' => $user->email,
                'error' => $e->getMessage()
            ], 'auth');

            $this->logActivity('فشل إرسال رمز التحقق', [
                'email' => $user->email,
                'error' => $e->getMessage()
            ], 'auth', $user, $user, 'email_verification');

            throw $e;
        }
    }
  public function verifyCodeById(int $userId, string $code): void
    {
        $user = $this->userRepository->findOrFail($userId);
        $this->verifyCode($user, $code);
    }
   public function verifyCode(User $user, string $code): void
{
    $context = 'email_verification';
    $this->rateLimiter->check($user->email, $context);

    $record = $this->repository->getByEmail($user->email);

    if (
        !$record ||
        !Hash::check($code, $record->code) ||
        (isset($record->expires_at) && now()->greaterThan($record->expires_at))
    ) {
        $this->logService->log('warning', 'رمز تحقق خاطئ أو منتهي', [
            'email' => $user->email,
            'ip' => request()->ip(),
        ], 'auth');

        $this->logActivity('رمز تحقق خاطئ أو منتهي', [
            'email' => $user->email
        ], 'auth', $user, $user, 'email_verification');

        throw new \Exception('رمز التحقق غير صحيح أو منتهي.');
    }

    $user->update(['email_verified_at' => now()]);
    $this->repository->delete($user->email);
    $this->rateLimiter->clear($user->email, $context);

    $this->logService->log('info', 'تم تفعيل البريد الإلكتروني بنجاح', [
        'user_id' => $user->id,
        'email' => $user->email,
        'ip' => request()->ip(),
    ], 'auth');

    $this->logActivity('تم تفعيل البريد الإلكتروني', [
        'user_id' => $user->id,
        'email' => $user->email
    ], 'auth', $user, $user, 'email_verification');
}

public function resendVerificationCodeById(int $userId): void
{
    $user = $this->userRepository->findOrFail($userId);

    if ($user->email_verified_at) {
        throw new \Exception('البريد الإلكتروني مفعل بالفعل.');
    }

    $this->sendVerificationCode($user);
}

  }

