<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback_student extends Model
{
       protected $fillable = ['booking_id', 'level', 'notes'];



  public function booking()
{
    return $this->belongsTo(Booking::class);
}

}
