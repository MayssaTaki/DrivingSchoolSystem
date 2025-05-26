<?php

namespace App\Policies;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TrainingReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Trainer $trainer): bool
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
    public function update(User $user, Trainer $trainer): bool
    {
    }

  public function approve(User $user, int $reviewId): bool
{
    return $user->role === 'employee';
}
    public function reject(User $user, Trainer $trainer): bool
    {
        return  $user->role === 'employee' ;
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Trainer $trainer): bool
    {
        return $user->id === $trainer->user_id || $user->role === 'employee';
    }
    

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Trainer $trainer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Trainer $trainer): bool
    {
        return false;
    }
}
