<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $users = User::where('role', 'employee')->get(); 

        foreach ($users as $user) {
            Employee::create([
                'user_id' => $user->id,
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'hire_date' => now(),
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'gender' => 'male', 
            ]);
        }
    }
}
