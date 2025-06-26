<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run()
    {
        $cars = [
            [
                'license_plate' => 'ABC123',
                'make' => 'Toyota',
                'model' => 'Corolla',
                'color' => 'Red',
                'year' => 2020,
                'transmission'=>'manual',
            ],
            [
                'license_plate' => 'XYZ789',
                'make' => 'Honda',
                'model' => 'Civic',
                'color' => 'Blue',
                'year' => 2021,
                'transmission'=>'manual',

            ],
            [
                'license_plate' => 'DEF456',
                'make' => 'Ford',
                'model' => 'Focus',
                'color' => 'Black',
                'year' => 2019,
                'transmission'=>'manual',

            ],
             [
                'license_plate' => 'DNF456',
                'make' => 'Ford',
                'model' => 'Focus',
                'color' => 'Black',
                'year' => 2019,
                'transmission'=>'automatic',
    'is_for_special_needs' => true,

            ],
                   [
                'license_plate' => 'DHF456',
                'make' => 'Ford',
                'model' => 'Focus',
                'color' => 'Black',
                'year' => 2019,
                'transmission'=>'automatic',
    'is_for_special_needs' => true,

            ],
        ];

        foreach ($cars as $car) {
            Car::create($car);
        }
    

       
    }
}