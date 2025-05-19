<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    protected $fillable = [
        'date_of_Birth',
        'address',
        'user_id',
        'first_name',
        'last_name',
         'phone_number',
          'image',
           'gender',
        
    ];
    public function bookings()
{
    return $this->hasMany(Booking::class);
}

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getImageAttribute($value)
{
    if ($value) {
        return asset('storage/' . $value);
    }

    return asset('images/default-user-image.webp');
}

public function reviews()
{
    return $this->hasMany(TrainerReview::class);
}

}
