<?php
namespace App\Events;

use App\Models\PracticalExamSchedule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PracticalExamScheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(public PracticalExamSchedule $schedule) {}
}
