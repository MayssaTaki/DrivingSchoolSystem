<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
   
    protected $fillable = [
        'trainer_id',
        'exception_date',
        'reason'
    ];

       protected $casts = [
        'exception_date' => 'date',
        
    ];
    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
