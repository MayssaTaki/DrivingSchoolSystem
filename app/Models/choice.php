<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class choice extends Model
{
   protected $fillable = ['question_id',
    'choice_text','is_correct'];
}
