<?php

namespace App\Services\Interfaces;

use App\Models\Employee;

interface EmployeeServiceInterface
{
    
    public function register(array $data): Employee;

    
    public function getAllEmployees(?string $name);


    public function delete(int $id): void;

    
    public function update(Employee $employee, array $data): Employee;

   
    public function clearEmployeeCache(): void;

    public function countEmployees(): int;
}
