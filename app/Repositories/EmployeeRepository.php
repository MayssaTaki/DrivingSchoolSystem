<?php

namespace App\Repositories;

use App\Models\Employee;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function getAllEmployees(?string $name, int $perPage = 10)
    {
        $page = request()->get('page', 1);
        $cacheKey = "employees_page_{$page}_name_" . ($name ?? 'all');

        return Cache::tags(['employees'])->remember($cacheKey, now()->addMinutes(10), function () use ($name, $perPage) {
            $query = Employee::with('user')->whereHas('user', fn($q) => $q->where('role', 'employee'));
            if ($name) {
                $query->where('first_name', 'like', "%{$name}%");
            }
            return $query->paginate($perPage);
        });
    }

    public function clearCache()
    {
        Cache::tags(['employees'])->flush();
    }

    public function deleteById(int $id): bool
    {
        return Employee::destroy($id) > 0;
    }

    public function findById(int $id): ?Employee
    {
        return Employee::with('user')->find($id);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        return $employee;
    }

    public function countEmployees(): int
    {
        return Cache::tags(['employees'])->remember('employees_count', now()->addMinutes(5), function () {
            return Employee::count();
        });
    }
}