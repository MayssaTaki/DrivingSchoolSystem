<?php
namespace App\Repositories\Contracts;
use App\Models\Student;

interface StudentRepositoryInterface
{
    public function create(array $data): Student;
    public function getAllStudents(?string $name, int $perPage = 10);
    public function clearCache();
    public function deleteById(int $id): bool;
    public function findById(int $id): ?Student;
    public function update(Student $student, array $data): Student;
    public function countStudents(): int;
public function find(int $id);


}