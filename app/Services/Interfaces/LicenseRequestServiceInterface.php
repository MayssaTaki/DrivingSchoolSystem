<?php

namespace App\Services\Interfaces;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
interface LicenseRequestServiceInterface
{
         public function requestLicense(array $data);
         public function getAllRequests(int $perPage = 10);
         public function getRequestsForCurrentStudent();
         public function approveRequest(int $requestId): bool;
         public function rejectRequest(int $requestId, string $reason): bool;
         public function getPendingRequests(): LengthAwarePaginator;
        public function getApprovedRequests(): LengthAwarePaginator;
        public function getRejectedRequests(): LengthAwarePaginator;
        public function countRejectedRequests();
        public function countApprovedRequests();
        public function countPendingRequests();
        public function getMonthlyReport(int $year, ?string $licenseCode, ?string $status): Collection;
    public function getTypeReport(): Collection;
public function getMostRequestedLicenses(int $limit = 2): Collection;

}