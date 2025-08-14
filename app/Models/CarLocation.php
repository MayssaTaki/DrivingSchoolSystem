<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarLocation extends Model
{
    protected $fillable = ['car_id', 'session_id','latitude', 'longitude', 'recorded_at'];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
    public function session()
{
    return $this->belongsTo(TrainingSession::class, 'session_id');
}

}
