<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class PracticalExamSchedulePolicy
{
     public function create(User $user): bool
    {
        return $user->role === 'employee';
    }
}