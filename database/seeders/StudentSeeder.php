<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $users = User::where('role', 'student')->get(); 

        foreach ($users as $user) {
            Student::create([
                'user_id' => $user->id,
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
               'date_of_birth'=>'1995-11-05',
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'gender' => 'male', 
            ]);
        }
    }
}
