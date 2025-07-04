<?php

namespace App\Repositories;
use Exception;
use DB;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Contracts\UserRepositoryInterface;
use Log;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{

  public function create(array $data): User
  {
      return User::create($data);
  }

  public function deleteById(int $id): bool
  {
      return User::destroy($id) > 0;
  }
public function findOrFail(int $id): User
{
    return User::findOrFail($id);
}





  public function findByEmail(string $email): ?User
  {
      return User::where('email', $email)->first();
  }
  
  public function update(User $user, array $data): User
  {
      $user->update($data);
      return $user;
  }

  

}
