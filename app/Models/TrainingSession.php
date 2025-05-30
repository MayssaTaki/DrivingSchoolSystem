<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
     protected $fillable = [
        'schedule_id', 'trainer_id', 'session_date',
        'start_time', 'end_time', 'status'
    ];
      public function schedule()
    {
        return $this->belongsTo(TrainingSchedule::class);
    }
    public function feedbacks()
{
    return $this->hasMany(Feedback_student::class);
}
        public function bookings()
{
    return $this->hasMany(Booking::class);
}

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
    
}
