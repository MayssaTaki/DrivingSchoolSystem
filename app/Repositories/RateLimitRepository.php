<?php
namespace App\Repositories;

use App\Repositories\Contracts\RateLimiterInterface;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitRepository implements RateLimiterInterface
{
    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    public function hit(string $key, int $decaySeconds = 60): int
    {
        return RateLimiter::hit($key, $decaySeconds);
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        return RateLimiter::remaining($key, $maxAttempts);
    }

    public function availableIn(string $key): int
    {
        return RateLimiter::availableIn($key);
    }

    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }

    public function attempt(string $key, int $maxAttempts, int $decaySeconds = 60): bool
    {
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return false;
        }
        return $this->hit($key, $decaySeconds) < $maxAttempts;
    }
}
