<?php
namespace App\Services;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

use App\Repositories\Contracts\RateLimiterInterface;
use Illuminate\Support\Str;

class RateLimitService
{
    protected RateLimiterInterface $limiter;

    public function __construct(RateLimiterInterface $limiter)
    {
        $this->limiter = $limiter;
    }

    public function throttleKey(string $identifier, string $context): string
    {
        $ip = request()?->ip() ?? '127.0.0.1';
        $agent = request()?->header('User-Agent') ?? 'unknown';
        return "{$context}|" . Str::lower($identifier) . "|{$ip}|" . md5($agent);
    }

    public function check(string $identifier, string $context): void
    {
        $key = $this->throttleKey($identifier, $context);
        $max = $this->getMaxAttempts($context);
        $decay = $this->getDecaySeconds($context);

        if ($this->limiter->tooManyAttempts($key, $max)) {
            $seconds = $this->limiter->availableIn($key);
        throw new ThrottleRequestsException("محاولات كثيرة جدًا، حاول بعد {$seconds} ثانية.");
        }

        $this->limiter->attempt($key, $max, $decay);
    }

    public function clear(string $identifier, string $context): void
    {
        $key = $this->throttleKey($identifier, $context);
        $this->limiter->clear($key);
    }

    protected function getMaxAttempts(string $context): int
    {
        return config("auth.throttle.{$context}.max_attempts", 5);
    }

    protected function getDecaySeconds(string $context): int
    {
        return config("auth.throttle.{$context}.decay_minutes", 5) * 60;
    }

    public function availableIn(string $identifier, string $context): int
    {
        $key = $this->throttleKey($identifier, $context);
        return $this->limiter->availableIn($key);
    }
}
