<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name'=>'admin',
            'email' => 'qyadaschool@gmail.com',
            'password' => bcrypt('qyadaschool@**'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
        ]);

        User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
        ]);

        User::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
        ]);

        User::create([
            'name' => 'User 4',
            'email' => 'user4@example.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
        ]);

        User::create([
            'name' => 'User 5',
            'email' => 'user5@example.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
        ]);
        User::create([
            'name' => 'User 6',
            'email' => 'user6@example.com',
            'password' => bcrypt('password123'),
            'role' => 'trainer',
        ]);
        User::create([
            'name' => 'User 7',
            'email' => 'user7@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);
        User::create([
            'name' => 'User 8',
            'email' => 'user8@example.com',
            'password' => bcrypt('password123'),
            'role' => 'trainer',
        ]);
        User::create([
            'name' => 'User 9',
            'email' => 'user9@example.com',
            'password' => bcrypt('password123'),
            'role' => 'trainer',
        ]);
        User::create([
            'name' => 'User 10',
            'email' => 'user10@example.com',
            'password' => bcrypt('password123'),
            'role' => 'trainer',
        ]);
    
        

       
    }
}