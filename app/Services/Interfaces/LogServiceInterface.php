<?php

namespace App\Services\Interfaces;

interface LogServiceInterface
{
   public function log(string $level, string $message, array $context = [], ?string $channel = null): void;

    public function getPaginatedLogs(int $perPage = 10, ?string $level = null, ?string $channel = null);
}
