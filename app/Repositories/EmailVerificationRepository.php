<?php

namespace App\Repositories;
use DB;
use Hash;
use App\Repositories\Contracts\EmailVerificationRepositoryInterface;
class EmailVerificationRepository implements EmailVerificationRepositoryInterface
{
   public function create(string $email, string $code, \Carbon\Carbon $expiresAt): void
{
    DB::table('email_verifications')->updateOrInsert(
        ['email' => $email],
        [
            'code' => Hash::make($code),
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}


    public function getByEmail(string $email)
    {
        return DB::table('email_verifications')->where('email', $email)->first();
    }

    public function delete(string $email): void
    {
        DB::table('email_verifications')->where('email', $email)->delete();
    }
}
