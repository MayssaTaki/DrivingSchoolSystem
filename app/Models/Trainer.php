<?php

namespace App\Models;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    protected $fillable = [
        
        'address',
        'user_id',
        'first_name',
        'last_name',
         'phone_number',
         'date_of_Birth',
           'gender',
           'image',
           'status'
          
        
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function deleteOldImage()
    {
        if ($this->image) {
            Storage::delete('public/' . $this->image);
        }
    }
     public function getImageAttribute($value)
{
    if ($value) {
        return asset('storage/' . $value);
    }

    return asset('images/default-user-image.webp');
}

public function trainingSchedules()
{
    return $this->hasMany(TrainingSchedule::class);
}

public function scheduleExceptions()
{
    return $this->hasMany(ScheduleException::class);
}
}
