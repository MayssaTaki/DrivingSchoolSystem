<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'title', 'body'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(PostFile::class);
    }

   

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function likedByUser($studentId)
    {
        return $this->likes()->where('student_id', $studentId)->exists();
    }
}
