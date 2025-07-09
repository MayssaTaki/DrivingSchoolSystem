<?php
namespace App\Events;

use App\Models\License;
use App\Models\Student;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LicenseRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Student $student,
        public License $license
    ) {}
}
