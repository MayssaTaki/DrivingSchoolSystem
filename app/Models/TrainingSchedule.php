<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSchedule extends Model
{
      protected $fillable = [
        'trainer_id',
        'day_of_week',
        'start_time',
        'end_time',
        
        'is_recurring',
        'valid_from',
        'valid_to',
        'status'
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
