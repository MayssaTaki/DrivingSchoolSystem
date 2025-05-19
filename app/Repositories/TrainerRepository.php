<?php

namespace App\Repositories;
use Exception;
use App\Models\User;
use App\Models\Trainer;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\TrainerRepositoryInterface;

class TrainerRepository  implements TrainerRepositoryInterface
{

  public function create(array $data): Trainer
  {
      return Trainer::create($data);
  }

  public function getAllTrainers(?string $name, int $perPage = 10)
  {
      $page = request()->get('page', 1);
      $cacheKey = "trainers_page_{$page}_name_" . ($name ?? 'all');

      return Cache::tags(['trainers'])->remember($cacheKey, now()->addMinutes(10), function () use ($name, $perPage) {
          $query = Trainer::with('user')
              ->whereHas('user', fn($q) => $q->where('role', 'trainer'));
          if ($name) {
              $query->where('first_name', 'like', "%{$name}%");
          }

          return $query->paginate($perPage);
      });
  }

  public function clearCache()
  {
      Cache::tags(['trainers'])->flush();
  }

  public function deleteById(int $id): bool
{
    return Trainer::destroy($id) > 0;
}

public function findById(int $id): ?Trainer
{
    return Trainer::with('user')->find($id);
}

public function update(Trainer $trainer, array $data): Trainer
{
    
    $trainer->update($data);
    return $trainer;
}

public function countTrainers(): int
{
    return Cache::tags(['trainers'])->remember('trainers_count', now()->addMinutes(5), function () {
        return Trainer::count();
    });
}

public function find($id): Trainer
{
    return Trainer::findOrFail($id);
}
public function approve(Trainer $trainer): Trainer
{
    if ($trainer->status !== 'pending') {
        throw new \Exception('Trainer status is not pending');
    }

    $trainer->status = 'approved';

    $trainer->save();

    
    return $trainer->fresh();
}

public function reject(Trainer $trainer): Trainer
{
    if ($trainer->status !== 'pending') {
        throw new \Exception('Trainer status is not pending');
    }

    $trainer->status = 'rejected';

    $trainer->save();

    
    return $trainer->fresh();
}

public function getApprovedTrainers()
{
    return Trainer::where('status', 'approved')->get();
}

public function getRejectedTrainers()
{
    return Trainer::where('status', 'rejected')->get();
}

public function getPendingTrainers()
{
    return Trainer::where('status', 'pending')->get();
}

public function getAllTrainersApprove(?string $name, int $perPage = 10)
  {
      $page = request()->get('page', 1);
      $cacheKey = "trainersApprove_page_{$page}_name_" . ($name ?? 'all');

      return Cache::tags(['trainers'])->remember($cacheKey, now()->addMinutes(10), function () use ($name, $perPage) {
          $query = Trainer::with('user')
              ->whereHas('user', fn($q) => $q->where('role', 'trainer'))
              ->where('status', 'approved');

              
          if ($name) {
              $query->where('first_name', 'like', "%{$name}%");
          }

          return $query->paginate($perPage);
      });
  }




  

}
