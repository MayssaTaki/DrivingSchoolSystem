<?php
namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CarSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        $sampleCars = [
            ['make' => 'تويوتا', 'model' => 'كورولا'],
            ['make' => 'هيونداي', 'model' => 'النترا'],
            ['make' => 'كيا', 'model' => 'سيراتو'],
            ['make' => 'نيسان', 'model' => 'صني'],
            ['make' => 'فورد', 'model' => 'فييستا'],
            ['make' => 'هوندا', 'model' => 'سيفيك'],
            ['make' => 'شيفروليه', 'model' => 'ماليبو'],
            ['make' => 'مازدا', 'model' => '3'],
            ['make' => 'جيلي', 'model' => 'امجراند'],
            ['make' => 'ميتسوبيشي', 'model' => 'لانسر'],
        ];

        for ($i = 0; $i < 15; $i++) {
            $carData = $faker->randomElement($sampleCars);
            $specialNeeds = $faker->boolean(30); 

            Car::create([
              'license_plate' => strtoupper($faker->unique()->bothify('??####?')),
                'make' => $carData['make'],
                'model' => $carData['model'],
                'color' => $faker->safeColorName,
                'year' => $faker->numberBetween(2015, 2023),
                'transmission' => $specialNeeds ? 'automatic' : $faker->randomElement(['automatic', 'manual']),
                'is_for_special_needs' => $specialNeeds,
                'status' => 'available',
            ]);
        }
    }
}
