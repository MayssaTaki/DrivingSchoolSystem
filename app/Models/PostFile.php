<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostFile extends Model
{
    protected $fillable = ['post_id', 'path', 'original_name', 'type'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
