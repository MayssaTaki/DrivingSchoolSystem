<?php

namespace App\Models;
use Illuminate\Support\Facades\Storage;

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
public function setImageAttribute($value)
{
    if (is_string($value) && str_starts_with($value, asset('storage'))) {
        $value = str_replace(asset('storage') . '/', '', $value);
    }
    $defaultImage = 'images/default-user-image.webp';

    if (
        $this->attributes['image'] ?? false &&
        $this->attributes['image'] !== $value &&
        !str_contains($this->attributes['image'], 'default-user-image') &&
        Storage::disk('public')->exists($this->attributes['image'])
    ) {
        Storage::disk('public')->delete($this->attributes['image']);
    }

    $this->attributes['image'] = $value;
}

public function reviews()
{
    return $this->hasMany(TrainerReview::class);
}
public function feedbacks()
{
    return $this->hasMany(Feedback_student::class);
}
}
