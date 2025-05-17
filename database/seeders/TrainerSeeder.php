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
                'gender' => 'male', 
                              'date_of_birth'=>'1995-11-05',

                'status'=>'pending',
            ]);
        }
    }
}
