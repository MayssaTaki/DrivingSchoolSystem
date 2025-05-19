<?php

namespace Database\Seeders;
use Database\Seeders\EmployeeSeeder;  

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        
        $this->call([
            \Database\Seeders\UserSeeder::class,
            \Database\Seeders\EmployeeSeeder::class,
            \Database\Seeders\TrainerSeeder::class,           
            \Database\Seeders\StudentSeeder::class,
            \Database\Seeders\CarSeeder::class,



        ]);
    }
}