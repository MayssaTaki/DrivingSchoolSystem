<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        User::create([
            'name' => 'admin',
            'email' => 'qyadaschool@gmail.com',
            'password' => bcrypt('qyadaschool@**'),
            'role' => 'admin',
        ]);

        foreach (range(1, 5) as $i) {
            User::create([
                'name' => $faker->name,
                'email' => "user{$i}@example.com",
                'password' => bcrypt('Password123'),
                'role' => 'employee',
            ]);
        }

        foreach (range(6, 36) as $i) {
            User::create([
                'name' => $faker->name,
                'email' => "user{$i}@example.com",
                'password' => bcrypt('Password123'),
                'role' => 'trainer',
            ]);
        }

      
        foreach (range(37, 137) as $i) {
            $fullName = $faker->firstName . ' ' . $faker->lastName;
            User::create([
                'name' => $fullName,
                'email' => "user{$i}@example.com",
                'password' => bcrypt('Password123'),
                'role' => 'student',
            ]);
        }
    }
}
