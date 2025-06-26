<?php

namespace App\Services\Interfaces;

interface LicenseServiceInterface
{
public function listLicenses(?string $code = null);
public function createLicense(array $data);
public function updateLicense(int $id, array $data);
public function clearLicenseCache(): void;
public function countLicenses(): int;
}