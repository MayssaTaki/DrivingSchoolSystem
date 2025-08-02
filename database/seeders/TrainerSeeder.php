<?php
namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TrainerSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        $users = User::where('role', 'trainer')->take(30)->get(); // نأخذ 30 فقط

        $pendingUsers = $users->slice(0, 10);
        $rejectedUsers = $users->slice(10, 10);
        $approvedUsers = $users->slice(20, 10);

        $this->createTrainers($pendingUsers, 'pending', $faker);
        $this->createTrainers($rejectedUsers, 'rejected', $faker);
        $this->createTrainers($approvedUsers, 'approved', $faker);
    }

    private function createTrainers($users, $status, $faker)
    {
        foreach ($users as $user) {
            $nameParts = explode(' ', $user->name);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            do {
                $licenseNumber = $faker->unique()->numerify('########');
            } while (Trainer::where('license_number', $licenseNumber)->exists());

            Trainer::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_number' => '05' . $faker->numerify('########'),
                'address' => $faker->city . '، ' . $faker->streetName,
                'gender' => $faker->randomElement(['male', 'female']),
                'date_of_birth' => $faker->date('Y-m-d', '2000-01-01'),
                'license_expiry_date' => $faker->date('Y-m-d', '2026-12-31'),
                'training_type' => $faker->randomElement(['normal', 'special_needs']),
                'license_number' => $licenseNumber,
                'experience' => $faker->numberBetween(1, 10) . ' سنوات خبرة في تعليم القيادة',
                'status' => $status,
            ]);
        }
    }
}
