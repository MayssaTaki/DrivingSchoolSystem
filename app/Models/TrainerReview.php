<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerReview extends Model
{
     protected $fillable = [
        'student_id', 'trainer_id', 'rating', 'comment', 'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
