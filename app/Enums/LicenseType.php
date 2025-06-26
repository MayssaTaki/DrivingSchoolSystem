<?php
namespace App\Enums;

enum LicenseType: string
{
    case PRIVATE_B = 'private_b';
    case PRIVATE_B1 = 'private_b1';
    case PUBLIC_C = 'public_c';
    case BUS_D1 = 'bus_d1';
    case TRUCK_D2 = 'truck_d2';
    case MOTOR_A = 'motor_a';
    case CONSTRUCTION_E = 'construction_e';

    public function label(): string
    {
        return match($this) {
            self::PRIVATE_B => 'رخصة خصوصي ب (عادي)',
            self::PRIVATE_B1 => 'رخصة خصوصي ب1 (أوتوماتيك)',
            self::PUBLIC_C => 'رخصة ج (عمومي)',
            self::BUS_D1 => 'رخصة د1 (باص كبير)',
            self::TRUCK_D2 => 'رخصة د2 (قاطرة ومقطورة)',
            self::MOTOR_A => 'رخصة أ (دراجة نارية)',
            self::CONSTRUCTION_E => 'رخصة أشغال (تريكسات)',
        };
    }
}
