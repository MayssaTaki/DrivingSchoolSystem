<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentTransaction;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PaymentTransactionSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $statuses = ['created', 'confirmed','refund_confirmed'];

        for ($i = 0; $i < 250; $i++) {
            PaymentTransaction::create([
                'invoice_id' => strtoupper(Str::random(10)), 
                'amount' => $faker->numberBetween(100, 5000), 
                'status' => Arr::random($statuses),
                'raw_response' => $faker->optional()->text(200),
                'guid' => $faker->optional()->uuid,
                'operation_number' => $faker->optional()->numerify('OP###'),
            ]);
        }
    }
}
