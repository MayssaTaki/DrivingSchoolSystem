<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'session_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function session()
    {
        return $this->belongsTo(TrainingSession::class);
    }
}
