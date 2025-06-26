<?php
namespace App\Repositories\Contracts;

interface LicenseRepositoryInterface
{
public function getAllOrByCode(?string $code = null);
public function create(array $data);
public function update(int $id, array $data);
public function findOrFail(int $id);
public function clearCache();
public function countLicenses(): int;

}