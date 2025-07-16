<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
   protected $fillable = [
        'booking_id',
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'polyline',
        'distance_in_meters',
        'duration_in_seconds',
        'start_address',
        'end_address'
    ];

    
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

   
    public function trainer()
    {
        return $this->booking->trainer ?? null;
    }
}
