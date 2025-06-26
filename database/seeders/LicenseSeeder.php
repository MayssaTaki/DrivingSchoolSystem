<?php

namespace Database\Seeders;

use App\Enums\LicenseType;
use App\Models\License;
use Illuminate\Database\Seeder;

class LicenseSeeder extends Seeder
{
    public function run()
    {
        $commonDocs = ['صورة شخصية', 'صورة عن الهوية الشخصية'];
        $withPrevLicense = array_merge($commonDocs, ['صورة عن الشهادة السابقة في حال امتلاك الشخص شهادة سابقة']);

        $licensesThatRequirePrevious = [
            LicenseType::PUBLIC_C->value,
            LicenseType::BUS_D1->value,
            LicenseType::TRUCK_D2->value,
        ];

        $baseData = [
            LicenseType::PRIVATE_B->value => [
                'min_age' => 18,
                'registration_fee' => 250000,
                'requirements' => [
                    'nationality' => 'syrian',
                    'allowed_for_military' => true,
                    'exam_schedule_days' => 25,
                ],
            ],
            LicenseType::PRIVATE_B1->value => [
                'min_age' => 18,
                'registration_fee' => 250000,
                'requirements' => [
                    'nationality' => 'syrian',
                    'allowed_for_military' => true,
                    'exam_schedule_days' => 25,
                ],
            ],
            LicenseType::PUBLIC_C->value => [
                'registration_fee' => 275000,
                'requirements' => [
                    'license_required' => 'private_b',
                    'license_years' => 3,
                    'nationality' => 'syrian',
                    'allowed_for_military' => false,
                    'military_note' => 'يجب أن يكون على البطاقة العسكرية رقم وطني ولا يسمح له بالتقديم على عمومي.',
                    'exam_schedule_days' => 30,
                ],
            ],
            LicenseType::BUS_D1->value => [
                'registration_fee' => 300000,
                'requirements' => [
                    'license_required' => 'public_c',
                    'license_years' => 2,
                    'nationality' => 'syrian',
                    'allowed_for_military' => false,
                    'exam_schedule_days' => 45,
                ],
            ],
            LicenseType::TRUCK_D2->value => [
                'registration_fee' => 300000,
                'requirements' => [
                    'license_required' => 'bus_d1',
                    'license_years' => 2,
                    'nationality' => 'syrian',
                    'allowed_for_military' => false,
                    'exam_schedule_days' => 45,
                ],
            ],
            LicenseType::MOTOR_A->value => [
                'registration_fee' => 300000,
                'requirements' => [
                    'nationality' => 'syrian',
                    'allowed_for_military' => false,
                    'exam_schedule_days' => 45,
                ],
            ],
            LicenseType::CONSTRUCTION_E->value => [
                'registration_fee' => 300000,
                'requirements' => [
                    'nationality' => 'syrian',
                    'allowed_for_military' => false,
                    'requires_video' => true,
                    'video_description' => 'فيديو واضح يتضمن قيادة التريكس يوم الامتحان',
                    'exam_schedule_days' => 7,
                ],
            ],
        ];

        foreach (LicenseType::cases() as $licenseType) {
            $code = $licenseType->value;
            $data = $baseData[$code];

            $requiredDocs = in_array($code, $licensesThatRequirePrevious) ? $withPrevLicense : $commonDocs;

            License::create([
                'code' => $code,
                'name' => $licenseType->label(),
                'min_age' => $data['min_age'] ?? null,
                'registration_fee' => $data['registration_fee'],
                'required_documents' => $requiredDocs,
                'requirements' => $data['requirements'],
            ]);
        }
    }
}
