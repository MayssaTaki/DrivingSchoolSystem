<?php

namespace Database\Seeders;

use App\Models\PracticalExamSchedule;
use App\Models\LicenseRequest;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Faker\Factory as Faker;

class PracticalExamScheduleSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        $statuses = ['scheduled', 'absent', 'failed', 'passed'];

        $licenseRequests = LicenseRequest::all();
        $employees = Employee::all();

        if ($licenseRequests->isEmpty() || $employees->isEmpty()) {
            $this->command->warn("لا توجد بيانات في جدول الطلبات أو الموظفين.");
            return;
        }

        for ($i = 0; $i < 15; $i++) {
        $request = $licenseRequests->random();
        $status = Arr::random($statuses);

        $examDate = $status === 'scheduled' 
                    ? now()->addDays(rand(1, 15)) 
                    : now()->subDays(rand(1, 15)); 

        $examTime = $faker->time('H:i');

        PracticalExamSchedule::create([
            'license_request_id' => $request->id,
            'employee_id' => $employees->random()->id,
            'exam_date' => $examDate->toDateString(),
            'exam_time' => $examTime,
            'status' => $status,
        ]);
    }

    }
}
