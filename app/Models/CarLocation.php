<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarLocation extends Model
{
    protected $fillable = ['car_id', 'latitude', 'longitude', 'recorded_at'];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
