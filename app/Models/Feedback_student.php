<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback_student extends Model
{
       protected $fillable = ['session_id', 'student_id', 'trainer_id', 'notes', 'rating', 'number_session'];



    public function session()
{
    return $this->belongsTo(TrainingSession::class, 'session_id');
}

    public function student() {
        return $this->belongsTo(Student::class);
    }

   public function trainer()
{
    return $this->belongsTo(Trainer::class);
}
}
