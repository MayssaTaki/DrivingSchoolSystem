<?php
namespace App\Events;

use App\Models\Feedback_student;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeedbackGiven
{
    use Dispatchable, SerializesModels;

    public function __construct(public Feedback_student $feedback) {}
}
