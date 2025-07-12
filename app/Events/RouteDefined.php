<?php
namespace App\Events;

use App\Models\Route;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RouteDefined
{
    use Dispatchable, SerializesModels;

    public function __construct(public Route $route) {}
}
