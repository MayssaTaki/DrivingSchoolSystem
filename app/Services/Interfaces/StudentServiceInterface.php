<?php

namespace App\Services\Interfaces;

use App\Models\Student;

interface StudentServiceInterface
{
    public function register(array $data): Student;

    public function getAllStudents(?string $name);

    public function delete(int $id): void;

    public function update(Student $student, array $data): Student;

    public function clearStudentCache(): void;

    public function countStudents(): int;
}
