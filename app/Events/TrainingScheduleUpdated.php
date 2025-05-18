<?php
namespace App\Events;

use App\Models\TrainingSchedule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrainingScheduleUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public TrainingSchedule $schedule) {}
}
