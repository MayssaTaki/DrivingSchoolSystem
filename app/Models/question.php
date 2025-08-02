<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class question extends Model
{

protected $fillable = ['image_path',
    'question_text','exam_id'];

  public function choices() {
    return $this->hasMany(Choice::class);
}

 public function examAttemptQuestions()
    {
        return $this->hasMany(ExamAttemptQuestion::class);
    }
public function getImageUrlAttribute(): ?string
{
    return $this->image_path 
        ? app(\App\Services\Interfaces\ImageServiceInterface::class)->getSignedUrl($this->image_path)
        : null;
}

}
