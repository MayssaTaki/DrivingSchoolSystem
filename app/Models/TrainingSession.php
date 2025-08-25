<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
     protected $fillable = [
        'schedule_id', 'trainer_id', 'session_date',
        'start_time', 'end_time', 'status','registration_fee'
    ];
      public function schedule()
    {
        return $this->belongsTo(TrainingSchedule::class);
    }

        public function bookings()
{
    return $this->hasMany(Booking::class,'session_id');
}
public function carLocations()
{
    return $this->hasMany(CarLocation::class, 'session_id');
}

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
    public function carReservations()
{
    return $this->hasMany(CarReservation::class, 'session_id');
}

}
