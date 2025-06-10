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
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
