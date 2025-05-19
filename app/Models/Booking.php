<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'car_id',
        'student_id',
        'trainer_id',
        'session_id',
        'status',
        
    ];
    public function student()
{
    return $this->belongsTo(Student::class);
}

public function trainer()
{
    return $this->belongsTo(Trainer::class);
}

public function car()
{
    return $this->belongsTo(Car::class);
}

public function session()
{
    return $this->belongsTo(TrainingSession::class, 'session_id');
}

}
