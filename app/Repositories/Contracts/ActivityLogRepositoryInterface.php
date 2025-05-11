<?php
namespace App\Repositories\Contracts;
use Illuminate\Pagination\LengthAwarePaginator;

interface ActivityLogRepositoryInterface
{
    public function getLogs(array $filters = [], int $perPage = 10): LengthAwarePaginator;

}