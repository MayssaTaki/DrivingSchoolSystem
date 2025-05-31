<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
   protected $fillable = ['exam_id', 'student_id', 'started_at', 'finished_at','score'];

    public function exam() {
        return $this->belongsTo(Exam::class);
    }

    public function student() {
        return $this->belongsTo(student::class, 'student_id'); 
    }
      public function questions()
    {
        return $this->hasMany(ExamAttemptQuestion::class);
    }
}
