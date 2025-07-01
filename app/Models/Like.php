<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['post_id', 'student_id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function student()
    {
        return $this->belongsTo(student::class);
    }
}
