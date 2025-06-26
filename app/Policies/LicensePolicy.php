<?php
namespace App\Policies;

use App\Models\User;
use App\Models\License;

class LicensePolicy
{
    public function create(User $user)
    {
        return $user->role === 'employee';
    }

    public function update(User $user, License $license)
    {
        return $user->role === 'employee';
    }

   
}
