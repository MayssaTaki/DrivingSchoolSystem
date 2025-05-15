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

       protected $casts = [
        'exception_date' => 'date',
        'is_available' => 'boolean',
        'available_start_time' => 'datetime:H:i',
        'available_end_time' => 'datetime:H:i',
    ];
    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
