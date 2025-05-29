<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class exam extends Model
{
     protected $fillable = ['type',
    'duration_minutes','trainer_id'];

    
   public function questions() {
    return $this->hasMany(Question::class);
}
public function trainer()
{
    return $this->belongsTo(User::class, 'trainer_id');
}

}
