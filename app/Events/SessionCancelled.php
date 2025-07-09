<?php
namespace App\Events;

use App\Models\Booking;
use App\Models\TrainingSession ;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public TrainingSession  $session,
        public bool $cancelledByStudent 
    ) {}
}
