<?php
namespace App\Events;

use App\Models\CarFault;
use App\Models\Car;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CarMarkedAsResolved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public CarFault $fault,
        public Car $car
    ) {}
}
