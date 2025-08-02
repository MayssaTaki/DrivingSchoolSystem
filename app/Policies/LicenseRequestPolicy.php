<?php
namespace App\Policies;

use App\Models\User;
use App\Models\LicenseRequest;

class LicenseRequestPolicy
{
   public function approve(User $user, LicenseRequest $licenseRequest)
{
    return $user->role === 'employee'|| $user->role === 'admin';
}

public function reject(User $user, LicenseRequest $licenseRequest)
{
    return $user->role === 'employee'|| $user->role === 'admin';
}


   
}
