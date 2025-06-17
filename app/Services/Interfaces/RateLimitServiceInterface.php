<?php

namespace App\Services\Interfaces;

interface RateLimitServiceInterface
{
    public function throttleKey(string $identifier, string $context): string;

    public function check(string $identifier, string $context): void;

    public function clear(string $identifier, string $context): void;

    public function availableIn(string $identifier, string $context): int;
}
