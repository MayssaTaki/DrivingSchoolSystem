<?php
namespace App\Repositories\Contracts;
use App\Models\Employee;

interface EmployeeRepositoryInterface
{
    public function create(array $data): Employee;
    public function getAllEmployees(?string $name, int $perPage = 10);
    public function clearCache();
    public function deleteById(int $id): bool;
    public function findById(int $id): ?Employee;
    public function update(Employee $employee, array $data): Employee;
    public function countEmployees(): int;



}