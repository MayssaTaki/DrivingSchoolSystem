<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PracticalExamSchedule;

use Illuminate\Auth\Access\Response;

class PracticalExamSchedulePolicy
{
     public function create(User $user): bool
    {
        return $user->role === 'employee';
    }
  public function update(User $user, PracticalExamSchedule $exam): bool
{
    return $user->role === 'employee';
}

}