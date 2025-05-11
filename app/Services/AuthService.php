<?php

namespace App\Services;

use App\Traits\LogsActivity;
use App\Repositories\Contracts\RefreshTokenRepositoryInterface;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
    use LogsActivity;

    protected RefreshTokenRepositoryInterface $refreshRepo;
    protected $logService;

    public function __construct(RefreshTokenRepositoryInterface $refreshRepo)
    {
        $this->refreshRepo = $refreshRepo;
        $this->logService = app()->make('App\Services\LogService');
    }

    public function login(array $credentials): array
    {
        $email = $credentials['email'];
        $remember = $credentials['remember_me'] ?? false;
        $key = $this->throttleKey($email, 'login');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->logService->log('warning', 'محاولات دخول كثيرة جدًا', [
                'email' => $email,
                'ip' => request()->ip(),
                'agent' => request()->userAgent(),
                'available_in_seconds' => $seconds,
            ], 'auth');
            abort(429, "محاولات كثيرة جدًا، حاول بعد {$seconds} ثانية.");
        }

        auth()->factory()->setTTL($remember ? 60 * 24 * 15 : 60);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                RateLimiter::hit($key, 60);
                $this->logService->log('error', 'فشل تسجيل الدخول - بيانات غير صحيحة', [
                    'email' => $email,
                    'ip' => request()->ip(),
                    'agent' => request()->userAgent(),
                ], 'auth');
                throw new AuthenticationException("بيانات الدخول غير صحيحة");
            }
        } catch (\Throwable $e) {
            $this->logService->log('error', 'حدث خطأ أثناء تسجيل الدخول', [
                'email' => $email,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'auth');
            throw $e;
        }

        RateLimiter::clear($key);

        $user = auth()->user();
        $refreshToken = Str::random(64);

        $this->refreshRepo->create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays($remember ? 15 : 1),
        ]);

        $this->logActivity('تم تسجيل الدخول', ['remember_me' => $remember], 'auth', $user, $user, 'login');

        $this->logService->log('info', 'تم تسجيل الدخول بنجاح', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
            'remember_me' => $remember,
        ], 'auth');

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'refresh_token' => $refreshToken,
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }

    public function refreshToken(): array
    {
        $incomingToken = request()->input('refresh_token');
        $key = $this->throttleKey(auth()->user()?->email ?? 'guest', 'refresh');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->logService->log('warning', 'طلب تجديد متكرر جداً', [
                'ip' => request()->ip(),
                'agent' => request()->userAgent(),
                'available_in_seconds' => $seconds,
            ], 'auth');
            abort(429, "طلب متكرر جداً، حاول بعد {$seconds} ثانية.");
        }

        $hashed = hash('sha256', $incomingToken);
        $record = $this->refreshRepo->findValidToken($hashed);

        if (!$record) {
            RateLimiter::hit($key, 60);
            $this->logService->log('error', 'توكن تجديد غير صالح أو منتهي', [
                'token_hash' => $hashed,
                'ip' => request()->ip(),
            ], 'auth');
            throw new AuthenticationException('توكن غير صالح أو منتهي.');
        }

        RateLimiter::clear($key);

        $this->logActivity('تم تجديد التوكن', [], 'auth', $record->user, $record->user, 'refresh');

        return [
            'token' => JWTAuth::fromUser($record->user),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }

    public function logoutUser(): void
    {
        try {
            $user = auth()->user();
            $token = JWTAuth::getToken();
            $ttl = auth()->factory()->getTTL() * 60;

            Redis::setex("blacklist:{$token}", $ttl, true);

            $this->refreshRepo->deleteByUserId($user->id);
            auth()->logout(true);

            $this->logActivity('تم تسجيل الخروج', [], 'auth', $user, $user, 'logout');

            $this->logService->log('info', 'تم تسجيل الخروج بنجاح', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
            ], 'auth');
        } catch (\Throwable $e) {
            $this->logService->log('error', 'خطأ أثناء تسجيل الخروج', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => auth()->user()?->only('id', 'email'),
            ], 'auth');
            throw $e;
        }
    }

    private function throttleKey(string $email, string $context): string
    {
        $ip = request()?->ip() ?? '127.0.0.1';
        $agent = request()?->header('User-Agent') ?? 'unknown';
        return "{$context}|" . Str::lower($email) . "|{$ip}|" . md5($agent);
    }
}