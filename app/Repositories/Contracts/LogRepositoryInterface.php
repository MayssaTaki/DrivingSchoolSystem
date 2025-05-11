<?php
namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LogRepositoryInterface
{
    public function store(array $data): void;

    public function getAllPaginated(int $perPage = 10, ?string $level = null, ?string $channel = null): LengthAwarePaginator;
}
