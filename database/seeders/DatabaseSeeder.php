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
            \Database\Seeders\TrainingScheduleSeeder::class,
         \Database\Seeders\ExamSeeder::class,
        \Database\Seeders\LicenseSeeder::class,
        \Database\Seeders\TrainerReviewSeeder::class,
        \Database\Seeders\TrainingSessionSeeder::class,
        \Database\Seeders\PostSeeder::class,








        ]);
    }
}