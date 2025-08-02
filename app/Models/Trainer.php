<?php

namespace App\Models;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
           'status',
           'license_number',
           'license_expiry_date',
           'training_type',
           'experience'
          
        
    ];

    public function bookings()
{
    return $this->hasMany(Booking::class);
}
public function routes()
{
    return $this->hasManyThrough(Route::class, Booking::class);
} 
public function exams()
{
    return $this->hasMany(Exam::class, 'trainer_id');
}

    public function user()
    {
        return $this->belongsTo(User::class);
    }
   
public function getImageAttribute($value)
{
    if (!$value) {
        return asset('images/default-user-image.webp');
    }

    if (Str::startsWith($value, 'http')) {
        return $value;
    }

    if (Storage::disk('public')->exists($value)) {
        return asset('storage/' . $value);
    }

    return app(\App\Services\Interfaces\ImageServiceInterface::class)->getSignedUrl($value);
}

public function getImageUrl(): string
{
    $original = $this->getRawOriginal('image');

    if (!$original) {
        return asset('images/default-user-image.webp'); 
    }

    if (Str::startsWith($original, 'http')) {
        return $original;
    }

    return app(\App\Services\Interfaces\ImageServiceInterface::class)
        ->getSignedUrl($original) ?? asset('images/default-user-image.webp'); // fallback
}



public function setImageAttribute($value)
{
    if (is_string($value) && str_starts_with($value, asset('storage'))) {
        $value = str_replace(asset('storage') . '/', '', $value);
    }

    $defaultImage = 'images/default-user-image.webp';

    if (
        ($this->attributes['image'] ?? false) &&
        $this->attributes['image'] !== $value &&
        !str_contains($this->attributes['image'], 'default-user-image') &&
        !Str::startsWith($this->attributes['image'], 'trainers/') && // ← صور Cloudinary
        Storage::disk('public')->exists($this->attributes['image'])
    ) {
        Storage::disk('public')->delete($this->attributes['image']);
    }

    $this->attributes['image'] = $value;
}

public function trainingSchedules()
{
    return $this->hasMany(TrainingSchedule::class);
}
public function trainingSession()
{
    return $this->hasMany(TrainingSession::class);
}
public function reviews()
{
    return $this->hasMany(TrainerReview::class);
}

public function scheduleExceptions()
{
    return $this->hasMany(ScheduleException::class);
}
 public function faultReports()
    {
        return $this->hasMany(CarFault::class);
    }
}
