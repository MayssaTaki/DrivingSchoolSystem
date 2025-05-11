<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
   
    protected $fillable = [
        'trainer_id',
        'exception_date',
        'is_available',
        'available_start_time',
        'available_end_time',
        'reason'
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
