<?php
namespace App\Events;

use App\Models\ScheduleException;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExceptionRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(public ScheduleException $exception) {}
}
