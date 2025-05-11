<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Repositories\RateLimitRepository;
use App\Exceptions\RateLimitExceededException;

class RateLimitService
{
    protected RateLimitRepository $rateLimitRepository;

    public function __construct(RateLimitRepository $rateLimitRepository)
    {
        $this->rateLimitRepository = $rateLimitRepository;
    }

    public function checkLoginRateLimit(string $email): void
    {
        $key = $this->generateKey('login', $email);
        $maxAttempts = config('ratelimit.login.max_attempts');
        $decaySeconds = config('ratelimit.login.decay_seconds');

        if ($this->rateLimitRepository->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->rateLimitRepository->availableIn($key);
            throw new RateLimitExceededException('login', $seconds);
        }

        $this->rateLimitRepository->hit($key, $decaySeconds);
    }

    
    public function checkRegistrationRateLimit(string $ipAddress): void
    {
        $key = $this->generateKey('register', $ipAddress);
        $maxAttempts = config('ratelimit.register.max_attempts');
        $decaySeconds = config('ratelimit.register.decay_seconds');

        if ($this->rateLimitRepository->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->rateLimitRepository->availableIn($key);
            throw new RateLimitExceededException('registration', $seconds);
        }

        $this->rateLimitRepository->hit($key, $decaySeconds);
    }

    
    public function checkEmployeeCreationRateLimit(string $adminId): void
    {
        $key = $this->generateKey('employee_create', $adminId);
        $maxAttempts = config('ratelimit.employee_creation.max_attempts');
        $decaySeconds = config('ratelimit.employee_creation.decay_seconds');

        if ($this->rateLimitRepository->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->rateLimitRepository->availableIn($key);
            throw new RateLimitExceededException('employee_creation', $seconds);
        }

        $this->rateLimitRepository->hit($key, $decaySeconds);
    }

    
    public function clearRateLimit(string $type, string $identifier): void
    {
        $key = $this->generateKey($type, $identifier);
        $this->rateLimitRepository->clear($key);
    }

   
    private function generateKey(string $type, string $identifier): string
    {
        $ip = request()->ip() ?? '127.0.0.1';
        $agent = request()->header('User-Agent') ?? 'unknown';
        
        return "{$type}|" . Str::lower($identifier) . "|{$ip}|" . md5($agent);
    }
}