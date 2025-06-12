<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarFault extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'trainer_id',
        'comment',
        'booking_id',
        'status'
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
      public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
