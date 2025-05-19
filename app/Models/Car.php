<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'license_plate',
        'make',
        'model',
        'color',
        'year',
        'image',
        'transmission',
        'is_for_special_needs',
        
        
    ];
    public function getCarTypeAttribute()
{
    return $this->is_for_special_needs ? 'سيارة احتياجات خاصة ♿' : 'سيارة عادية 🚗';
}
public function bookings()
{
    return $this->hasMany(Booking::class);
}

}
