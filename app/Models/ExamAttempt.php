<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
   protected $fillable = ['exam_id', 'student_id', 'started_at', 'submitted_at'];

    public function exam() {
        return $this->belongsTo(Exam::class);
    }

    public function student() {
        return $this->belongsTo(student::class, 'student_id'); 
    }
}
