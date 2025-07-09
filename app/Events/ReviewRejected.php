<?php
namespace App\Events;

use App\Models\TrainerReview;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(public TrainerReview $review) {}
}
