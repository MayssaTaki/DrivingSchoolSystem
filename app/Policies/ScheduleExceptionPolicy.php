<?php

namespace App\Policies;

use App\Models\ScheduleException;
use App\Models\User;
use App\Models\Trainer;

use Illuminate\Auth\Access\Response;

class ScheduleExceptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAllExceptions(User $user, Trainer $trainer): bool
{
    return $user->id === $trainer->user_id||
           $user->role==='admin' ||
           $user->role==='employee';

}
 public function approve(User $user, ScheduleException $exception): bool
{
    return $user->role === 'employee';
}

public function reject(User $user, ScheduleException $exception): bool
{
    return $user->role === 'employee';
}

    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScheduleException $scheduleException): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScheduleException $scheduleException): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduleException $scheduleException): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ScheduleException $scheduleException): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ScheduleException $scheduleException): bool
    {
        return false;
    }
}
