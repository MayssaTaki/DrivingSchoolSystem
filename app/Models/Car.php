<?php

namespace App\Models;
use Illuminate\Support\Facades\Storage;

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
public function setImageAttribute($value)
{
if (is_string($value) && str_starts_with($value, asset('storage'))) {
        $value = str_replace(asset('storage') . '/', '', $value);
    }
    if (
        $this->attributes['image'] ?? false &&
        $this->attributes['image'] !== $value &&
        Storage::disk('public')->exists($this->attributes['image'])
    ) {
        Storage::disk('public')->delete($this->attributes['image']);
    }

    $this->attributes['image'] = $value;
}
   public function faultReports()
    {
        return $this->hasMany(CarFault::class);
    }
}
