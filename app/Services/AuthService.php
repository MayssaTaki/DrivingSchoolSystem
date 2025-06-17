<?php

namespace App\Services;

use App\Traits\LogsActivity;
use App\Repositories\Contracts\RefreshTokenRepositoryInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\AuthenticationException;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\LogServiceInterface;

use App\Services\Interfaces\RateLimitServiceInterface;

use App\Services\RateLimitService;

class AuthService implements AuthServiceInterface 
{
    use LogsActivity;

    protected RefreshTokenRepositoryInterface $refreshRepo;
    protected LogServiceInterface $logService;
    protected RateLimitServiceInterface $rateLimiter;

    public function __construct(
    RefreshTokenRepositoryInterface $refreshRepo,
    RateLimitServiceInterface $rateLimiter,
    LogServiceInterface $logService
) {
    $this->rateLimiter = $rateLimiter;
    $this->refreshRepo = $refreshRepo;
    $this->logService = $logService;
}


    public function login(array $credentials): array
    {
        $email = $credentials['email'];
        $remember = $credentials['remember_me'] ?? false;

        
        $this->rateLimiter->check($email, 'login');

        auth()->factory()->setTTL($remember ? 60 * 24 * 15 : 60);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
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

       
        $this->rateLimiter->clear($email, 'login');

        $user = auth()->user();
        $role = $user->role;
 if (in_array($role, ['trainer', 'employee', 'student'])) {
        $user->load($role);
    }
        $refreshToken = Str::random(64);

        $this->refreshRepo->create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays($remember ? 15 : 1),
            'role' => $role
        ]);

        $this->logActivity('تم تسجيل الدخول', ['remember_me' => $remember], 'auth', $user, $user, 'login');

        $this->logService->log('info', 'تم تسجيل الدخول بنجاح', [
          
            'email' => $user->email,
            'ip' => request()->ip(),
            'remember_me' => $remember,
        ], 'auth');

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'refresh_token' => $refreshToken,
            'expires_in' => auth()->factory()->getTTL() * 60,
            'role' => $role
        ];
    }

    public function refreshToken(): array
    {
        $email = auth()->user()?->email ?? 'guest';
        $context = 'refresh';

        $this->rateLimiter->check($email, $context);

        $incomingToken = request()->input('refresh_token');
        $hashed = hash('sha256', $incomingToken);

        $record = $this->refreshRepo->findValidToken($hashed);

        if (!$record) {
            $this->logService->log('error', 'توكن تجديد غير صالح أو منتهي', [
                'token_hash' => $hashed,
                'ip' => request()->ip(),
            ], 'auth');

            throw new AuthenticationException('توكن غير صالح أو منتهي.');
        }

        $this->rateLimiter->clear($email, $context);

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
}
