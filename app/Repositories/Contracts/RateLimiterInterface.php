<?php
namespace App\Repositories\Contracts;

interface RateLimiterInterface
{
    public function attempt(string $key, int $maxAttempts, int $decaySeconds = 60): bool;
    public function tooManyAttempts(string $key, int $maxAttempts): bool;
    public function availableIn(string $key): int;
    public function clear(string $key): void;
    public function hit(string $key, int $decaySeconds = 60): int;
    public function remaining(string $key, int $maxAttempts): int;
}
