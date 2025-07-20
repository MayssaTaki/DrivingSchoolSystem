<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Feedback_student;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;

class FeedbackStudentsSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        $levels = ['beginner', 'intermediate', 'excellent'];
        $notesSamples = [
            'الأداء جيد جداً، استمر على هذا المستوى.',
            'يحتاج إلى تحسين بعض المهارات.',
            'ممتاز وتقدم ملحوظ.',
            'يمكنه التطور أكثر مع الممارسة.',
            null, 
        ];

        $completedBookings = Booking::where('status', 'completed')
            ->whereDoesntHave('feedback')  
            ->get();

        foreach ($completedBookings as $booking) {
            Feedback_student::create([
                'booking_id' => $booking->id,
                'level' => Arr::random($levels),
                'notes' => Arr::random($notesSamples),
            ]);
        }
    }
}
