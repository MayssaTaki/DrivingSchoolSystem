<?php

namespace App\Repositories;
use Exception;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\StudentRepositoryInterface;

class StudentRepository  implements StudentRepositoryInterface
{

  public function create(array $data): Student
  {
      return Student::create($data);
  }

  public function getAllStudents(?string $name, int $perPage = 10)
  {
      $page = request()->get('page', 1);
      $cacheKey = "studets_page_{$page}_name_" . ($name ?? 'all');

      return Cache::tags(['students'])->remember($cacheKey, now()->addMinutes(10), function () use ($name, $perPage) {
          $query = Student::with('user')
              ->whereHas('user', fn($q) => $q->where('role', 'student'));

          if ($name) {
              $query->where('first_name', 'like', "%{$name}%");
          }

          return $query->paginate($perPage);
      });
  }

  public function clearCache()
  {
      Cache::tags(['students'])->flush();
  }

  public function deleteById(int $id): bool
{
    return Student::destroy($id) > 0;
}

public function findById(int $id): ?Student
{
    return Student::with('user')->find($id);
}

public function update(Student $student, array $data): Student
{
    $student->update($data);
    return $student;
}

public function countStudents(): int
{
    return Cache::tags(['students'])->remember('students_count', now()->addMinutes(5), function () {
        return Student::count();
    });
}






  

}
