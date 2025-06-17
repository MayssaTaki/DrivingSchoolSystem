<?php
namespace App\Services\Interfaces;
use Illuminate\Pagination\LengthAwarePaginator;

interface ActivityLogServiceInterface
{
        public function getPaginatedLogs(array $filters = [], int $perPage = 10): LengthAwarePaginator;

}