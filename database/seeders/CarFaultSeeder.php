<?php
namespace Database\Seeders;

use App\Models\Car;
use App\Models\User;
use App\Models\Booking;
use App\Models\CarFault;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CarFaultSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        $cars = Car::inRandomOrder()->take(5)->get();
        $trainers = User::where('role', 'trainer')->inRandomOrder()->get();
        $statuses = ['new', 'in_progress', 'resolved'];
        $comments = [
            'يوجد صوت غير طبيعي في المحرك.',
            'تعطل في نظام الفرامل.',
            'إضاءة التنبيه على لوحة العدادات تعمل.',
            'مكيف الهواء لا يعمل.',
            'تسريب زيت أسفل السيارة.',
        ];

        foreach ($cars as $index => $car) {
            $trainer = $trainers->random();
            $booking = Booking::inRandomOrder()->first(); 
            $faultStatus = $faker->randomElement($statuses);

            CarFault::create([
                'car_id' => $car->id,
                'trainer_id' => $trainer->id,
                'booking_id' => $faker->boolean(70) ? optional($booking)->id : null,
                'comment' => $comments[$index],
                'status' => $faultStatus,
            ]);

            $car->update([
                'status' => $faultStatus === 'in_progress' ? 'in_repair' : 'available',
            ]);
        }
    }
}
