<?php
namespace App\Events;

use App\Models\Trainer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrainerApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public Trainer $trainer) {}
}
