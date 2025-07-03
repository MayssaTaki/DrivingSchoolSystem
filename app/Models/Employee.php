<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'hire_date',
        'phone_number',
        'address',
        'user_id',
        'first_name',
        'last_name',
        'gender'
        
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
        public function practicalExamSchedules()
{
    return $this->hasMany(PracticalExamSchedule::class);
}
}
