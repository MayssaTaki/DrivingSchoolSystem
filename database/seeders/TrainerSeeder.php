<?php

namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TrainerSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $users = User::where('role', 'trainer')->get(); 

        foreach ($users as $user) {

            // توليد رقم رخصة فريد
            do {
                $licenseNumber = $faker->unique()->numerify('#######'); // 7 أرقام
            } while (Trainer::where('license_number', $licenseNumber)->exists());

            Trainer::create([
                'user_id' => $user->id,
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'phone_number' => $faker->unique()->phoneNumber,
                'address' => $faker->address,
                'gender' => $faker->randomElement(['male', 'female']),
                'date_of_birth' => '1995-11-05',
                'license_expiry_date' => '2026-11-05',
                'training_type' => 'normal',
                'license_number' => $licenseNumber,
                'experience' => '2 years in driving school',
                'status' => 'pending',
            ]);
        }
    }
}
