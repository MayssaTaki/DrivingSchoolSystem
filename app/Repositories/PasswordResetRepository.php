<?php

namespace App\Repositories;
use App\Repositories\Contracts\PasswordResetRepositoryInterface;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetRepository implements PasswordResetRepositoryInterface
{
    public function storeResetToken(string $email, string $token)
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );
    }

    

    public function getByEmail(string $email)
    {
        return DB::table('password_reset_tokens')->where('email', $email)->first();
    }

    public function delete(string $email)
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }
}