<?php
namespace App\Events;

use App\Models\CarFault;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CarFaultSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public CarFault $fault) {}
}
