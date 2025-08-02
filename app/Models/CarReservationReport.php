<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarReservationReport extends Model
{
    protected $fillable = ['car_id', 'date', 'total_reservations'];
}
