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
        $faker = Faker::create('ar_SA'); 

        $users = User::where('role', 'student')->get();

        foreach ($users as $user) {
    $nameParts = explode(' ', $user->name);
    $firstName = $nameParts[0];
    $lastName = $nameParts[1] ?? '';  
            Student::create([
                'user_id' => $user->id,
                'first_name' =>$firstName,
                'last_name' => $lastName,
                'date_of_birth' => $faker->date('Y-m-d', '1990-01-01'), 
                'phone_number' => '05' . $faker->numerify('########'),
                'address' => $faker->city . '، ' . $faker->streetName,
                'gender' => $faker->randomElement(['male', 'female']),
            ]);
        }
    }
}
