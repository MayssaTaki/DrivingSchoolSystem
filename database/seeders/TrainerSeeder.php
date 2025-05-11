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
            Trainer::create([
                'user_id' => $user->id,
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'experience' => '2 years in Qyada School',
                'gender' => 'male', 
                'license_number' => $faker->numerify('#########'),
                'specialization' => 'regular', 
                'license_expiry_date'=>now(),
                'status'=>'pending',
            ]);
        }
    }
}
