<?php
namespace App\Repositories\Contracts;
use App\Models\LicenseRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface LicenseRequestRepositoryInterface
{

    public function create(array $data): LicenseRequest;
public function getAllPaginated(int $perPage = 10);
    public function getByStudent(int $studentId);
public function updateStatus(int $requestId, string $status, ?string $notes = null): bool;
public function findById(int $id): LicenseRequest;
public function findByStatus(string $status): LengthAwarePaginator;
public function countByStatus(string $status);
public function monthlyCounts(int $year, ?string $licenseCode = null, ?string $status = null): Collection;
    public function typeStatistics(): Collection;
public function mostRequestedLicenses(int $limit = 2): Collection;

}