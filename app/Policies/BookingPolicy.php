<?php

namespace App\Policies;

use App\Models\Car;
use App\Models\User;
use App\Models\Booking;

use Illuminate\Auth\Access\Response;

class BookingPolicy
{


public function complete(User $user, Booking $booking): bool
{
    return $user->role === 'trainer'
        && $user->trainer 
        && $user->trainer->id === $booking->trainer_id;
}


public function start(User $user, Booking $booking): bool
{
    return $user->role === 'trainer'
        && $user->trainer 
        && $user->trainer->id === $booking->trainer_id;
}


    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {

    }
    

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user): bool
    {
        return false;
    }
}
